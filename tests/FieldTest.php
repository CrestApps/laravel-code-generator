<?php

namespace CrestApps\CodeGenerator\Tests;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class FieldTest extends TestCase
{
    /** @test */
    public function testEloquentDataMethodForBigInt()
    {
        $sourceString = 'name:foo_count;data-type:bigint';

        $fields = FieldTransformer::fromString($sourceString, 'generic');
        $this->assertTrue(is_array($fields) && 1 == count($fields));
        $field = $fields[0];

        $expected = 'bigInteger';
        $actual = $field->getEloquentDataMethod();
        $this->assertEquals($expected, $actual);
    }

    public function testEloquentDataMethodForBigInteger()
    {
        $sourceString = 'name:foo_count;data-type:biginteger';

        $fields = FieldTransformer::fromString($sourceString, 'generic');
        $this->assertTrue(is_array($fields) && 1 == count($fields));
        $field = $fields[0];

        $expected = 'bigInteger';
        $actual = $field->getEloquentDataMethod();
        $this->assertEquals($expected, $actual);
    }
}
