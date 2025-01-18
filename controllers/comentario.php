<?php
include 'conexion.php'; // Archivo que maneja la conexión a la base de datos

function agregarComentario($idDenuncia, $idUsuario, $comentario) {
    $conexion = abrirConexion();
    $nombreUsuarioQuery = "SELECT nombreUsuario FROM Usuario WHERE idUsuario = ?";
    $stmt = $conexion->prepare($nombreUsuarioQuery);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $stmt->bind_result($nombreUsuario);
    $stmt->fetch();
    $stmt->close();

    if ($nombreUsuario) {
        $query = "INSERT INTO Comentario (com, fecha, hora, idDenuncias, idUsuario, nombreUsuario) VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("siis", $comentario, $idDenuncia, $idUsuario, $nombreUsuario);
        if ($stmt->execute()) {
            echo "Comentario agregado exitosamente.";
        } else {
            echo "Error al agregar comentario: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "El usuario no existe.";
    }
    cerrarConexion($conexion);
}
?>
