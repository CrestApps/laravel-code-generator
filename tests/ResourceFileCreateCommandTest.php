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
        $this->mockOutFileSystem();

        // arguments we're passing in
        $fieldString = 'name:foo_count;data-type:bigint';

        // now call Artisan
        Artisan::call('resource-file:create', ['model-name' => 'TestModel', '--fields' => $fieldString]);
        // Vacuous assertion to give PHPUnit something to do instead of complaining about a risky test
        $this->assertTrue(true);
    }

    public function testCreateResourceFileWithBigIntegerField()
    {
        $this->mockOutFileSystem();

        // arguments we're passing in
        $fieldString = 'name:foo_count;data-type:biginteger';

        // now call Artisan
        Artisan::call('resource-file:create', ['model-name' => 'TestModel', '--fields' => $fieldString]);
        // Vacuous assertion to give PHPUnit something to do instead of complaining about a risky test
        $this->assertTrue(true);
    }

    public function testCreateResourceFileWithMorphedByManyRelation()
    {
        $this->mockOutFileSystem();

        // arguments we're passing in
        $relString = 'name:foo;type:morphedByMany;params:App\Foo|fooable';

        // now call Artisan
        Artisan::call('resource-file:create', ['model-name' => 'TestModel', '--relations' => $relString]);
        // Vacuous assertion to give PHPUnit something to do instead of complaining about a risky test
        $this->assertTrue(true);
    }

    /**
     * Mock out the file system so we don't accidentally scribble on something
     *
     * @return void
     */
    private function mockOutFileSystem()
    {
        File::shouldReceive('exists')->andReturnNull();
        File::shouldReceive('put')->andReturnNull();
        File::shouldReceive('isDirectory')->andReturn(false);
        File::shouldReceive('makeDirectory')->andReturnNull();
    }
}
