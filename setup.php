<?php

use Slim\LogWriter;
use SlimController\Slim;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use rmatil\server\Middleware\SecurityMiddleware;

require 'vendor/autoload.php';

// enable this for log writing to file
$logWriter = new LogWriter(fopen(__DIR__.'/logs/server.log', 'a'));

// Prepare app
$app = new Slim(array(
    'debug'                      => true, // enable slim exception handler
    'log.level'                  => \Slim\Log::DEBUG,
    'log.enabled'                => true, // enable logging
    'controller.class_prefix'    => 'rmatil\server\Controller',
    'controller.class_suffix'    => 'Controller',
    'controller.method_suffix'   => 'Action',
    'controller.template_suffix' => 'php',
    'log.writer'                 => $logWriter, // enable this for log writing to file
    'templates.path'             => '../templates'
));

$app->add(new SecurityMiddleware());


$fs = new Filesystem();
$filePath = __DIR__ . "/html/addresses/addresses.json";

$app->filePath = $filePath;

$app->container->singleton('fs', function () use ($fs) {
    return $fs;
});

$app->addRoutes(array(
    '/'                     => array('get'     => 'App:listIpAddresses'),
    '/ip-addresses'         => array('get'     => 'App:listIpAddresses'),
    '/ip-addresses/new'     => array('post'    => 'App:insertIpAddress'),
    '/ip-addresses/remove'  => array('post'    => 'App:removeIpAddress'),
    '/keepalive'            => array('get'     => 'App:keepAlive'),
));

// Run app
$app->run();
