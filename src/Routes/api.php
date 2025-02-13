<?php

use App\Controllers\TournamentController;
use App\Controllers\PlayerController;
use App\Controllers\MatchController;use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/api/tournaments', [TournamentController::class, 'getAll']);
    $app->get('/api/tournaments/{id}', [TournamentController::class, 'getById']);
    $app->post('/api/tournaments', [TournamentController::class, 'create']);

    $app->get('/api/players', [PlayerController::class, 'getAll']);
    $app->get('/api/players/{id}', [PlayerController::class, 'getById']);
    $app->post('/api/players', [PlayerController::class, 'create']);

    $app->get('/api/matches/{tournament_id}', [MatchController::class, 'getByTournament']);

    $app->get('/docs', function (Request $request, Response $response) {
        $openapi = Generator::scan([__DIR__ . '/../Controllers']);
        $response->getBody()->write($openapi->toJson());
        return $response->withHeader('Content-Type', 'application/json');
    });
};
