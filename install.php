<?php

// Создать пустую директорию для отчетов, если она еще не существует
$path = __DIR__ . DIRECTORY_SEPARATOR . 'report';

if (!is_dir($path)) {
    mkdir($path, 0755);
}

// Создать конфигурационный файл из шаблона, если он еще не существует
$configDist = __DIR__ . DIRECTORY_SEPARATOR . 'config.php.dist';
$config = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

if (!is_file($config)) {
    copy($configDist, $config);
}
