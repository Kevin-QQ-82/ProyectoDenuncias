<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($nombre, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO Usuario (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $hashedPassword);

        if ($stmt->execute()) {
            return ['message' => 'Usuario registrado exitosamente'];
        } else {
            return ['error' => 'Error al registrar usuario'];
        }
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT idUsuario, password FROM Usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            return ['error' => 'Usuario no encontrado'];
        }

        $stmt->bind_result($idUsuario, $hashedPassword);
        $stmt->fetch();

        if (!password_verify($password, $hashedPassword)) {
            return ['error' => 'Contraseña incorrecta'];
        }

        // Generar un token JWT
        $token = $this->generateToken($idUsuario);

        // Guardar el token en la base de datos
        $this->storeToken($idUsuario, $token);

        return ['token' => $token];
    }

    private function generateToken($idUsuario) {
        $payload = [
            "iat" => time(),
            "exp" => time() + 3600, // 1 hora de expiración
            "idUsuario" => $idUsuario
        ];
        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    private function storeToken($idUsuario, $token) {
        $stmt = $this->conn->prepare("UPDATE Usuario SET token = ? WHERE idUsuario = ?");
        $stmt->bind_param("si", $token, $idUsuario);
        $stmt->execute();
    }
}
?>
