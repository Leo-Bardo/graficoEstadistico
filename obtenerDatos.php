<?php
include ('conexion.php');
$con = conectar();

// Habilitar el reporte de errores para ver mensajes detallados
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar si la conexión es exitosa
if ($con->connect_error) {
    die("Conexión fallida: " . $con->connect_error);
}

// Consulta SQL para obtener el último registro
$query = "SELECT arregloGrafico, producto, maquina, ls, li, codigo, caducidad, fecha, hora, operador, supervisor, estado
          FROM control_estadistico_envasado
          ORDER BY fecha DESC, hora DESC LIMIT 1";
$result = $con->query($query);

// Verificar si se obtuvieron resultados
if ($result->num_rows > 0) {
    // Obtener los datos
    $row = $result->fetch_assoc();

    // Extraer los campos relevantes
    $producto = $row['producto'];
    $maquina = $row['maquina'];
    $ls = $row['ls']; // Límite superior
    $li = $row['li']; // Límite inferior
    $codigo = $row['codigo'];
    $caducidad = $row['caducidad'];
    $fecha = $row['fecha'];
    $estado = $row['estado']; // Nuevo campo estado
    $arregloGrafico = $row['arregloGrafico']; // El JSON
  
    // Decodificar el JSON y verificar si es válido
    $grafico_data = json_decode($arregloGrafico, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error en la decodificación del JSON: " . json_last_error_msg());
    }

    // Preparar los datos para el gráfico
    $datos = array(
        'producto' => $producto,
        'maquina' => $maquina,
        'ls' => $ls,
        'li' => $li,
        'codigo' => $codigo,
        'caducidad' => $caducidad,
        'fecha' => $fecha,
        'estado' => $row['estado'], // Añadir el estado
        'grafico_data' => $grafico_data // Esto es el array decodificado del arreglo JSON
    );

    // Devolver los datos como JSON
    echo json_encode($datos);
} else {
    die("No se encontraron resultados");
}

// Cerrar la conexión
$con->close();
?>
