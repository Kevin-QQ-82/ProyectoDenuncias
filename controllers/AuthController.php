<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos']);
            return;
        }

        $response = $this->user->register($data['nombre'], $data['email'], $data['password']);
        http_response_code(isset($response['error']) ? 500 : 201);
        echo json_encode($response);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos']);
            return;
        }

        $response = $this->user->login($data['email'], $data['password']);
        http_response_code(isset($response['error']) ? 401 : 200);
        echo json_encode($response);
    }
}
?>
