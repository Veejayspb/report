<?php
declare(strict_types=1);

use Veejay\Container\Container;
use Veejay\Report\Application;
use Veejay\Report\Component\Config;

require_once __DIR__ . '/vendor/autoload.php';

$definitions = require __DIR__ . '/src/Config/definitions.php';
$config = require __DIR__ . '/config.php';

$container = new Container($definitions);
$container->set(Config::class, fn() => new Config($config));

$application = $container->get(Application::class); /* @var Application $application */
$application->run();
