<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18/03/19
 * Time: 3:20 AM
 */

namespace CrestApps\CodeGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return ['CrestApps\CodeGenerator\CodeGeneratorServiceProvider'];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('database.connections.mysql', [
                'driver'   => 'mysql',
                'database' => 'twitter', // specify a DB that exists in your MySQL here
                'host' => 'localhost',
                'port' => 3306,
                'username'   => 'root',
                'password'   => '',
        ]);

        $app['config']->set('database.default', 'sqlite');
    }
}
