<?php

use App\Controllers\TournamentController;
use App\Models\BaseModel;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/TournamentController.php';
require_once __DIR__ . '/../src/Models/BaseModel.php';

BaseModel::initDatabase();
$pdo = BaseModel::$db;
$tournamentController = new TournamentController($pdo);

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('POST', '/api/tournament/simulate', 'simulateTournament');
    $r->addRoute('GET', '/api/tournament/completed', 'getCompletedTournaments');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        if (method_exists($tournamentController, $handler)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $tournamentController->$handler($input);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Handler no encontrado"]);
        }
        break;
}
