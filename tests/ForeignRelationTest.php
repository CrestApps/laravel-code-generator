<?php

namespace CrestApps\CodeGeneratorTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;

class ForeignRelationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
	 /** @test */
    public function testAbilityToCreateRelationForSingleField()
    {
		$relation = ForeignRelationship::fromString("name:fooModel;is-nullable:true;data-type:varchar;foreign-relation:assets#hasMany#App\\Models\\Asset|category_id|id");
		
		// TO DO, asset that the relation is created successfully!
    }
}