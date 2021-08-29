<?php
namespace App\Controllers;

use App\Services\Spotify;
use GuzzleHttp\Client;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SpotifyController extends APIController
{
    protected $view;
    protected $logger;
    protected $client;
    protected $spotify;

    public function __construct(Twig $view, LoggerInterface $logger, Client $client, Spotify $spotify)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->client = $client;
        $this->spotify = $spotify;
    }

    public function authenticate(Request $request, Response $response, $args)
    {
        try {
            $result = $this->spotify->generateToken();
            return $this->respondResult($response, $result);

        } catch(\Exception $e) {
            $this->logger->error('[SpotifyController]::getAlbums:: Error: '.$e->getMessage());
            return $this->respondWithError($response, 500, $e->getMessage());
        }
    }


    public function getAlbums(Request $request, Response $response, $args)
    {
        try {
            $q = $request->getParam('q');
            $result = $this->spotify->albums($q);

            return $this->respondResult($response, $result);

        } catch(\Exception $e) {
            $this->logger->error('[SpotifyController]::getAlbums:: Error: '.$e->getMessage());
            return $this->respondWithError($response, 500, $e->getMessage());
        }
    }

    public function getArtist(Request $request, Response $response, $args)
    {
        try {
            $result = $this->spotify->artist($request->getParam('q'));

            return $this->respondResult($response, $result);

        } catch(\Exception $e) {
            $this->logger->error('[SpotifyController]::getArtist:: Error: '.$e->getMessage());
            return $this->respondWithError($response, 500, $e->getMessage());
        }
    }
}
