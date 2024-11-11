<?php 
// Iniciar la sesión para acceder a la variable de sesión
// session_start();
include("conexion.php");
// session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>VALIDACIÓN</h1>
        <h2>SELECCIONA UNA OPCIÓN</h2>
        <!-- <a href="controlEstadisticoEnvasado.php">
            <div class="button">
                <img src="img/estadistica.png" alt="Administración">
                <br>
                CONTROL ESTADÍSTICO
            </div>
        </a> -->
        <a href="generarReporteEST.php" target="_blank">
            <div class="button">
                <img src="img/grafico.png" alt="Administración">
                <br>
                REPORTE ESTADÍSTICO
            </div>
        </a>
        <!-- <a href="controlEstadisticoEnvasado.php">
            <div class="button">
                <img src="img/historial.png" alt="Administración">
                <br>
                HISTORIAL
            </div>
        </a> -->
        <!-- <a href="generaGrafico.php">
            <div class="button">
                <img src="img/grafica.png" alt="Administración">
                <br>
                GRÁFICA
            </div>
        </a> -->
        <a href="../index.php">
            <div class="button">
                <img src="img/salir.jpg" alt="Regresar" id="salirButton"><!-- TAREA: programar boton para salir del programa y programar funcion para pantalla completa y ocultar botones de minimizar, maximizar y cerrar del navegador -->
                <br>
                REGRESAR
            </div>
        </a>
</body>
</html>