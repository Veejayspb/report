<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Veejay\Container\Container;
use Veejay\Report\Component\Config;
use Veejay\Report\Git\Git;
use Veejay\Report\Git\RepositoriesCollection;

return [
    ContainerInterface::class => function (Container $container) {
        return $container;
    },
    Git::class => function (Container $container) {
        $config = $container->get(Config::class); /* @var Config $config */
        return new Git($config->gitExe);
    },
    RepositoriesCollection::class => function (Container $container) {
        $git = $container->get(Git::class); /* @var Git $git */
        return new RepositoriesCollection($git);
    },
];
