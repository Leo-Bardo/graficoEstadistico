<?php

session_start(); // Iniciar sesión para acceder a las variables de sesión
include ('../conexion.php');
$con = conectar();

header('Content-Type: application/json'); // Asegura que la respuesta sea JSON

if ($con) {
    // Decodificar JSON
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar que todos los campos requeridos estén presentes
    if (empty($data['producto']) || empty($data['envasadora']) || empty($data['ls']) || 
        empty($data['li']) || empty($data['codigo']) || empty($data['caducidad']) || 
        empty($data['valores'])) {
        exit();
    }

    $producto = $data['producto'];
    $equipo = $data['envasadora'];
    $ls = $data['ls'];
    $li = $data['li'];
    $codigo = $data['codigo'];
    $caducidad = $data['caducidad'];
    $arregloEstadisticaEnvasado = json_encode($data['valores']);

    // Obtener el ID del usuario logueado desde la sesión
    $operador = isset($_SESSION['idUsuario']) ? $_SESSION['idUsuario'] : null; 
    $supervisor = 256; // Aquí podrías asignar un supervisor real si es necesario

    if (!$operador) {
        // Si no hay operador logueado, devolver un error
        $response = ["success" => false, "message" => "No hay un usuario logueado"];
        echo json_encode($response);
        exit();
    }

    date_default_timezone_set('America/Mexico_City');
    $fechaHoy = date("Y-m-d");
    $hora = date("H:i:s");

    $sql = "INSERT INTO control_estadistico_envasado (arregloGrafico, producto, maquina, ls, li, codigo, caducidad, fecha, hora, operador, supervisor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssssss", $arregloEstadisticaEnvasado, $producto, $equipo, $ls, $li, $codigo, $caducidad, $fechaHoy, $hora, $operador, $supervisor);
        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Cambios subidos correctamente"];
        } else {
            $response = ["success" => false, "message" => "Error al ejecutar la consulta: " . $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ["success" => false, "message" => "Error en la preparación de la consulta: " . $con->error];
    }
    $con->close();
} else {
    $response = ["success" => false, "message" => "No se pudo conectar a la base de datos"];
}

echo json_encode($response); // Enviar respuesta JSON
?>
