<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01/03/2019
 * Time: 05:30.
 */

namespace AppBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

trait WebTestRefreshDatabaseTrait
{
    protected static $application;

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }
}
