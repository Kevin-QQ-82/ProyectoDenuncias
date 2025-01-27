<?php

require_once "./controllers/ApiController.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
$segments = explode('/', $path);

$table = $segments[0] ?? null;
$id = $segments[1] ?? null;

if ($table) {
    $api = new ApiController();
    $api->handleRequest($method, $table, $id);
} else {
    //http_response_code(404);
    echo json_encode(['error' => 'Recurso no encontrado']);
}
