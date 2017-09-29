<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

class ControllerCommandBase extends Command
{
    use CommonCommand, GeneratorReplacers;

    /**
     * Checks if the ConvertEmptyStringsToNull middleware is registered or not
     *
     * @param string $string
     *
     * @return string
     */
    protected function isConvertEmptyStringsToNullRegistered()
    {
        $kernal = $this->getLaravel()->make(\App\Http\Kernel::class);

        return $kernal->hasMiddleware(\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class);
    }

    /**
     * Gets the code that call the file-upload's method.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getFillables(array $fields)
    {
        $names = [];

        foreach ($fields as $field) {
            if ($field->isOnFormView && !$field->isFile()) {
                $names[] = sprintf("'%s'", $field->name);
            }
        }

        return sprintf('[%s]', implode(',', $names));
    }

    /**
     * Gets laravel ready field validation format from a giving string
     *
     * @param string $validations
     *
     * @return string
     */
    protected function getValidationRules(array $fields)
    {
        $validations = '';

        foreach ($fields as $field) {

            $rules = $field->getValidationRule();
            $customRules = $this->extractCustomValidationRules($rules);

            if (!empty($rules)) {

                if (!empty($customRules)) {

                    $standardRules = array_diff($rules, $customRules);
                    $shortCustomRules = $this->extractCustomValidationShortName($customRules);

                    $wrappedRules = array_merge(Helpers::wrapItems($standardRules), $shortCustomRules);

                    $validations .= sprintf("        '%s' => [%s],\n    ", $field->name, implode(',', $wrappedRules));
                } else {
                    $validations .= sprintf("        '%s' => '%s',\n    ", $field->name, implode('|', $rules));
                }
            }
        }

        return $validations;
    }

    /**
     * Extracts the custom validation rules' short name from the giving rules array.
     *
     * @param array $rules
     *
     * @return array
     */
    protected function extractCustomValidationShortName(array $rules)
    {
        $customRules = array_map(function ($rule) {
            $fullname = $this->getCustomRuleFullName($rule);

            if ($this->canHaveUsingCommand($fullname)) {
                $shortName = $this->getCustomRuleShortName($fullname);

                return $this->makeCustomRuleCall($shortName);
            }

            return $this->makeCustomRuleCall($fullname);

        }, $rules);

        return $customRules;
    }

    /**
     * Extracts the custom validation rules' short name from the giving rules array.
     *
     * @param array $rules
     *
     * @return array
     */
    protected function extractCustomValidationNamespaces(array $rules)
    {
        $customRules = array_filter($rules, function ($rule) {
            $fullname = $this->getCustomRuleFullName($rule);

            return $this->canHaveUsingCommand($fullname);
        });

        $customRules = array_map(function ($rule) {
            return $this->getCustomRuleFullName($rule);
        }, $customRules);

        return array_unique($customRules);
    }

    /**
     * Extracts the custom validation rules from the giving rules array.
     *
     * @param array $rules
     *
     * @return array
     */
    protected function extractCustomValidationRules(array $rules)
    {
        $customRules = array_filter($rules, function ($rule) {
            return $this->isCustomRule($rule);
        });

        return $customRules;
    }

    /**
     * Checks if the givin rule is a custom validation rule
     *
     * @param string $rule
     *
     * @return bool
     */
    protected function isCustomRule($rule)
    {
        return starts_with(trim($rule), 'new ');
    }

    /**
     * Make a custom rule call
     *
     * @param string $rule
     *
     * @return string
     */
    protected function makeCustomRuleCall($rule)
    {
        return sprintf('new %s', $rule);
    }

    /**
     * Get the short name of the giving custom validation rule.
     *
     * @param string $rule
     *
     * @return string
     */
    protected function getCustomRuleShortName($rule)
    {
        $name = $this->getCustomRuleFullName($rule);

        return class_basename($name);
    }

    /**
     * Checks if a class name starts with a slash \
     *
     * @param string $fullname
     *
     * @return bool
     */
    protected function canHaveUsingCommand($fullname)
    {
        return !starts_with($fullname, '\\');
    }

    /**
     * Get the full class name of the giving custom valiation rule.
     *
     * @param string $rule
     *
     * @return string
     */
    protected function getCustomRuleFullName($rule)
    {
        return str_replace(['new ', ';', ' '], '', trim($rule));
    }

    /**
     * Gets the code that is needed to check for bool property.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getBooleanSnippet(array $fields, $requestVariable = '$this')
    {
        $code = '';

        foreach ($fields as $field) {
            if ($field->isBoolean() && $field->isCheckbox()) {
                $code .= sprintf("        \$data['%s'] = %s->has('%s');", $field->name, $requestVariable, $field->name) . PHP_EOL;
            }
        }

        return $code;
    }

    /**
     * Gets the code that call the file-upload's method.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getFileSnippet(array $fields, $requestName = '$this')
    {
        $code = '';
        $template = <<<EOF
        if (%s->has('custom_delete_%s')) {
            \$data['%s'] = %s;
        }
        if (%s->hasFile('%s')) {
            \$data['%s'] = \$this->moveFile(%s->file('%s'));
        }

EOF;

        foreach ($fields as $field) {
            if ($field->isFile()) {
                $code .= sprintf($template,
                    $requestName,
                    $field->name,
                    $field->name,
                    $field->isNullable() ? 'null' : "''",
                    $requestName,
                    $field->name,
                    $field->name,
                    $requestName,
                    $field->name);
            }
        }

        return $code;
    }

    /**
     * Gets the code that is needed to convert empty string to null.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getStringToNullSnippet(array $fields)
    {
        if ($this->isConvertEmptyStringsToNullRegistered()) {
            return '';
        }

        $code = '';

        foreach ($fields as $field) {
            if ($field->isNullable && !$field->isPrimary && !$field->isAutoIncrement && !$field->isRequired() && !$field->isBoolean() && !$field->isFile()) {
                $code .= sprintf("        \$data['%s'] = !empty(\$request->input('%s')) ? \$request->input('%s') : null;", $field->name, $field->name, $field->name) . PHP_EOL;
            }
        }

        return $code;
    }

    /**
     * Gets the method's stub that handels the file uploading.
     *
     * @param array $fields
     * @param bool $withFormRequest
     *
     * @return string
     */
    protected function getUploadFileMethod(array $fields, $withFormRequest = false)
    {
        if (!$withFormRequest && Config::createMoveFileMethod() && $this->containsfile($fields)) {
            $stubName = 'controller-upload-method';
            if (Helpers::isNewerThanOrEqualTo()) {
                $stubName .= '-5.3';
            }

            return $this->getStubContent($stubName, $this->getTemplateName());
        }

        return '';
    }

    /**
     * Replaces the file snippet for the given stub.
     *
     * @param $stub
     * @param $snippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub, $snippet)
    {
        $stub = $this->strReplace('file_snippet', $snippet, $stub);

        return $this;
    }

    /**
     * Replaces the fillable snippet for the given stub.
     *
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replaceFillables(&$stub, $fillable)
    {
        $stub = $this->strReplace('fillable', $fillable, $stub);

        return $this;
    }

    /**
     * Replaces the boolean snippet for the given stub.
     *
     * @param  string  $stub
     * @param  string  $snippet
     *
     * @return $this
     */
    protected function replaceBooleadSnippet(&$stub, $snippet)
    {
        $stub = $this->strReplace('boolean_snippet', $snippet, $stub);

        return $this;
    }

    /**
     * Replaces useCommandPlaceHolder
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceUseCommandPlaceholder(&$stub, $commands)
    {
        $stub = $this->strReplace('use_command_placeholder', $commands, $stub);

        return $this;
    }

    /**
     * Replaces the form-request's name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $snippet
     *
     * @return $this
     */
    protected function replaceStringToNullSnippet(&$stub, $snippet)
    {
        $stub = $this->strReplace('string_to_null_snippet', $snippet, $stub);

        return $this;
    }

    /**
     * Replaces the request_name_comment for the given stub.
     *
     * @param $stub
     * @param $comment
     *
     * @return $this
     */
    protected function replaceRequestNameComment(&$stub, $comment)
    {
        $stub = $this->strReplace('request_name_comment', $comment, $stub);

        return $this;
    }

    /**
     * Gets the comment for the request name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getRequestNameComment($name)
    {
        return sprintf('@param %s %s ', $name, $this->requestVariable);
    }

    /**
     * Replaces the visibility level of a giving stub
     *
     * @param  string  $stub
     * @param  string  $level
     *
     * @return $this
     */
    protected function replaceMethodVisibilityLevel(&$stub, $level)
    {
        $stub = $this->strReplace('visibility_level', $level, $stub);

        return $this;
    }

    /**
     * Replaces get_data_method template.
     *
     * @param  string  $stub
     * @param  string  $code
     *
     * @return $this
     */
    protected function replaceGetDataMethod(&$stub, $code)
    {
        $stub = $this->strReplace('get_data_method', $code, $stub);

        return $this;
    }

}
