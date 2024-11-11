<?php
include("../conexion.php");

$con = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de los datos enviados desde el formulario
    $producto = $_POST['producto'];
    $envasadora = $_POST['envasadora'];
    $codigo = $_POST['codigo'];
    $caducidad = $_POST['caducidad'];
    $li = $_POST['li'];
    $ls = $_POST['ls'];
    $arregloGraficoJson = $_POST['arregloGrafico'] ?? '[]';

    // Fecha y hora actuales
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");
    $estado = 1; // Estado activo

    // Preparación de la consulta SQL
    $sql = "INSERT INTO control_estadistico_envasado (producto, maquina, codigo, caducidad, li, ls, arregloGrafico, fecha, hora, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssddssss", $producto, $envasadora, $codigo, $caducidad, $li, $ls, $arregloGraficoJson, $fecha, $hora, $estado);

    try {
        $stmt->execute();
        header("Location: ./historialCEE.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        echo "Error: " . $e->getMessage();
    }

    $stmt->close();
    $con->close();
} else {
    echo "Acceso no autorizado.";
}
