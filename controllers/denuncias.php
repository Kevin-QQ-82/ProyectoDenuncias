<?php
include 'conexion.php'; // Archivo que maneja la conexión a la base de datos

function consultarHistorialDenunciasPorFecha($fechaInicio, $fechaFin) {
    $conexion = abrirConexion();
    $query = "SELECT * FROM Denuncia WHERE fecha BETWEEN ? AND ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    while ($fila = $resultado->fetch_assoc()) {
        echo "Denuncia ID: " . $fila['idDenuncia'] . " - Tipo: " . $fila['tipo'] . " - Descripción: " . $fila['descripcion'] . "<br>";
    }
    
    $stmt->close();
    cerrarConexion($conexion);
}
?>
