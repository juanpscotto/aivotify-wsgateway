<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class Spotify
{

    protected $logger;
    protected $client;
    protected $token;

    public function __construct(LoggerInterface $logger, Client $client)
    {
        $this->logger = $logger;
        $this->client = $client;
    }

    public function generateToken() : array
    {
        try {
            $this->logger->info('[Spotify]::generateToken:: Starting Service');

            $return = [
                'access_token' => null,
                'token_type' => null,
                'expires_in' => null
            ];

            $CLIENT_ID = $_ENV['CLIENT_ID'];
            $CLIENT_SECRET = $_SERVER['CLIENT_SECRET'];

            $headerCode = base64_encode($CLIENT_ID.':'.$CLIENT_SECRET);

            $headers = [
                'Authorization' => "Basic {$headerCode}"
            ];

            $formParams = [
                'grant_type' => 'client_credentials'
            ];

            $result = $this->client->request('POST', 'https://accounts.spotify.com/api/token', [
                'headers' => $headers,
                'form_params' => $formParams
            ]);

            $responseArr = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->debug('[SpotifyController]::generateToken:: responseArr: '.print_r($responseArr, true));

            if ($result->getStatusCode() === 200) {
                $this->logger->info('[SpotifyController]::generateToken:: body: '.print_r($responseArr, true));
                if (isset($responseArr['access_token'])) {
                    $this->logger->warning('[SpotifyController]::generateToken:: token: '.$responseArr['access_token']);
                    $this->token = $responseArr['access_token'];
                    $return['access_token'] = $responseArr['access_token'];
                }
                if (isset($responseArr['token_type'])) {
                    $return['token_type'] = $responseArr['token_type'];
                }
                if (isset($responseArr['expires_in'])) {
                    $return['expires_in'] = $responseArr['expires_in'];
                }
            }

            $this->logger->info('[Spotify]::generateToken:: return: '.print_r($return, true));
            $this->logger->info('[Spotify]::generateToken:: Ending Service');

            return $return;

        } catch (GuzzleException $e) {
            $this->logger->error('[Spotify]::generateToken:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::generateToken:: Ending Service');
            return [
                'access_token' => null,
                'token_type' => null,
                'expires_in' => null
            ];
        } catch(\Exception $e) {
            $this->logger->error('[Spotify]::generateToken:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::generateToken:: Ending Service');
            return [
                'access_token' => null,
                'token_type' => null,
                'expires_in' => null
            ];
        }

    }


    public function artist($q) : array
    {
        try {
            $this->logger->info('[Spotify]::artist:: Starting Service');

            $return = [
                'artist' => null
            ];

            $this->generateToken();

            $this->logger->info($this->token);

            $headers = [
                'Authorization' => "Bearer {$this->token}"
            ];

            $query = [
                'q' => $q,
                'type' => 'artist',
                'market' => 'US',
                'limit' => 1
            ];

            $result = $this->client->request('GET', 'https://api.spotify.com/v1/search', [
                'headers' => $headers,
                'query' => $query
            ]);
            $responseArr = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->debug('[SpotifyController]::generateToken:: responseArr: '.print_r($responseArr, true));

            if ($result->getStatusCode() === 200) {
                if (isset($responseArr['artists'])) {
                    $artist = array_shift($responseArr['artists']['items']);
                    $artistClean = [
                        'id' =>  $artist['id'],
                        'name' => $artist['name'],
                        'href' => $artist['href'],
                    ];
                    $return['artist'] = $artistClean;
                }
            }

            $this->logger->info('[Spotify]::artist:: return: '.print_r($return, true));
            $this->logger->info('[Spotify]::artist:: Ending Service');

            return $return;

        } catch (GuzzleException $e) {
            $this->logger->error('[Spotify]::artist:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::artist:: Ending Service');
            return [
                'artist' => null,
            ];
        }  catch(\Exception $e) {
            $this->logger->error('[Spotify]::artist:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::artist:: Ending Service');
            return [
                'artist' => null,
            ];
        }

    }


    public function albums($q) : array
    {
        try {
            $this->logger->info('[Spotify]::albums:: Starting Service');

            $return = [
                'artist' => null,
                'albums' => [],
            ];
            $this->generateToken();

            $this->logger->debug('[Spotify]::albums:: q: '.$q);
            $responseArtist = $this->artist($q);
            if (empty($responseArtist['artist'])) {
                return $return;
            }
            $return['artist'] = $responseArtist['artist'];

            $headers = [
                'Authorization' => "Bearer {$this->token}"
            ];

            $query = [
                'market' => 'US'
            ];

            $id = $responseArtist['artist']['id'];

            $url = "https://api.spotify.com/v1/artists/{$id}/albums";
            $result = $this->client->request('GET', $url, [
                'headers' => $headers,
                'query' => $query
            ]);

            $responseArr = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->debug('[SpotifyController]::albums:: responseArr: '.print_r($responseArr, true));

            if ($result->getStatusCode() === 200) {
                if (isset($responseArr['items'])) {
                    $albums = array_map(function($item) {
                        return [
                            'name' => $item['name'],
                            'released' => $item['released'],
                            'tracks' => $item['total_tracks'],
                            'cover' =>array_shift($item['images'])
                        ];
                    }, $responseArr['items']);
                    $return['albums'] = $albums;
                }
            }

            $this->logger->info('[Spotify]::albums:: return: '.print_r($return, true));
            $this->logger->info('[Spotify]::albums:: Ending Service');

            return $return;

        } catch (GuzzleException $e) {
            $this->logger->error('[Spotify]::albums:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::albums:: Ending Service');
            return [
                'artist' => null,
                'albums' => [],
            ];
        }  catch(\Exception $e) {
            $this->logger->error('[Spotify]::albums:: Error: '.$e->getMessage());
            $this->logger->info('[Spotify]::albums:: Ending Service');
            return [
                'artist' => null,
                'albums' => [],
            ];
        }
    }
}
