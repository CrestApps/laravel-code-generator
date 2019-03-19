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

    public function testAutoIncrementFalseIsHonouredWithUnderscores()
    {
        $sourceString = 'name:id;data-type:varchar;is_primary:true;is_auto_increment:false;is_nullable:false;data-type-params:5000';

        $fields = FieldTransformer::fromString($sourceString, 'generic', []);
        $this->assertTrue(is_array($fields) && 1 == count($fields));
        $field = $fields[0];

        $this->assertFalse($field->isAutoIncrement);
    }

    public function testAutoIncrementFalseIsHonouredWithHyphens()
    {
        $sourceString = 'name:id;data-type:varchar;is-primary:true;is-auto-increment:false;is-nullable:false;data-type-params:5000';

        $fields = FieldTransformer::fromString($sourceString, 'generic', []);
        $this->assertTrue(is_array($fields) && 1 == count($fields));
        $field = $fields[0];

        $this->assertFalse($field->isAutoIncrement);
    }
}
