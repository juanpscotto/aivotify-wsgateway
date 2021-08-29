<?php
// DIC configuration

use App\Logging\LogFormatter;
use GuzzleHttp\Client;

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Flash messages
$container['flash'] = function ($c) {
    return new Slim\Flash\Messages;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    foreach ($logger->getHandlers() as $handler) {
        $handler->setFormatter(new LogFormatter(null, null, true, true));
    }
    return $logger;
};

// Guzzle
$container['client'] = function($c) {
    return new Client([
        // Base URI is used with relative requests
//        'base_uri' => $_ENV['SPOTIFY_BASE_API'],
        // You can set any number of default request options.
        'timeout'  => 2.0,
    ]);
};

// -----------------------------------------------------------------------------
// Controllers
// -----------------------------------------------------------------------------

$container[App\Controllers\HomeController::class] = function($c) {
    return new App\Controllers\HomeController($c->get('view'), $c->get('logger'));
};

$container[App\Controllers\SpotifyController::class] = function($c) {
    $spotify = new \App\Services\Spotify($c->get('logger'), $c->get('client'));
    return new App\Controllers\SpotifyController($c->get('view'), $c->get('logger'), $c->get('client'), $spotify);
};
