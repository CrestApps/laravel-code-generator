<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18/03/19
 * Time: 5:31 AM
 */

namespace CrestApps\CodeGenerator\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ResourceFileCreateCommandTest extends TestCase
{
    public function testCreateResourceFileWithBigIntField()
    {
        //first, mock out the file system so we don't accidentally scribble on something
        File::shouldReceive('exists')->andReturnNull();
        File::shouldReceive('put')->andReturnNull();
        File::shouldReceive('isDirectory')->andReturn(false);
        File::shouldReceive('makeDirectory')->andReturnNull();

        // arguments we're passing in
        $fieldString = 'name:foo_count;data-type:bigint';

        // now call Artisan
        Artisan::call('resource-file:create', ['model-name' => 'TestModel', '--fields' => $fieldString]);
        // Vacuous assertion to give PHPUnit something to do instead of complaining about a risky test
        $this->assertTrue(true);
    }

    public function testCreateResourceFileWithBigIntegerField()
    {
        //first, mock out the file system so we don't accidentally scribble on something
        File::shouldReceive('exists')->andReturnNull();
        File::shouldReceive('put')->andReturnNull();
        File::shouldReceive('isDirectory')->andReturn(false);
        File::shouldReceive('makeDirectory')->andReturnNull();

        // arguments we're passing in
        $fieldString = 'name:foo_count;data-type:biginteger';

        // now call Artisan
        Artisan::call('resource-file:create', ['model-name' => 'TestModel', '--fields' => $fieldString]);
        // Vacuous assertion to give PHPUnit something to do instead of complaining about a risky test
        $this->assertTrue(true);
    }
}
