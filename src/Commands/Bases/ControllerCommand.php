<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

class ControllerCommand extends Command
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
     * Gets the code that is needed to check for bool property.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getBooleanSnippet(array $fields)
    {
        $code = '';

        foreach ($fields as $field) {
            if ($field->isBoolean() && $field->isCheckbox()) {
                $code .= sprintf("        \$data['%s'] = %s->has('%s');", $field->name, $this->requestVariable, $field->name) . PHP_EOL;
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
    protected function getFileSnippet(array $fields)
    {
        $code = '';
        $template = <<<EOF
        if (\$request->hasFile('%s')) {
            \$data['%s'] = \$this->moveFile(\$request->file('%s'));
        }
EOF;

        foreach ($fields as $field) {
            if ($field->isFile()) {
                $code .= sprintf($template, $field->name, $field->name, $field->name);
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
            return $this->getStubContent('controller-upload-method', $this->getTemplateName());
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

    /**
     * Checks if current Laravel's Version is >= 5.5
     *
     * @return string
     */
    protected function isLaravel55OrUp()
    {
        return Helpers::isNewerThan('5.4.9999999999');
    }
}
