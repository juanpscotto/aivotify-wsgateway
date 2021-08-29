<?php
// Routes

use App\Controllers\HomeController;
use App\Controllers\SpotifyController;
use Slim\App;

$app->get('/', HomeController::class . ':home')->setName('home');


$app->group('/api/v1', function (App $app) {
    $app->post('/authenticate', SpotifyController::class. ':authenticate')->setName('api.authenticate');
    $app->post('/albums', SpotifyController::class. ':getAlbums')->setName('api.getAlbums');
    $app->post('/artist', SpotifyController::class. ':getArtist')->setName('api.getArtist');
});


