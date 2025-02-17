<?php

require __DIR__ . "/../vendor/autoload.php";

use OpenApi\Generator;
use OpenApi\Annotations as OA; // Importar las anotaciones de Swagger

header('Content-Type: application/json');

// 🚨 Forzar escaneo de los controladores y verificar si Swagger detecta anotaciones
$openapi = Generator::scan([realpath(__DIR__ . '/../src/Controllers')]);

// 🚨 Depurar si Swagger no encontró anotaciones
if (!$openapi->info) {
    die(json_encode(["error" => "Falta la anotación @OA\Info() en los controladores."], JSON_PRETTY_PRINT));
}

if (empty($openapi->paths)) {
    die(json_encode(["error" => "Falta la anotación @OA\PathItem() en los controladores."], JSON_PRETTY_PRINT));
}

// Si todo está bien, mostrar JSON de Swagger
echo $openapi->toJson();
