<?php
// Incluye la biblioteca FPDF
require "../../../fpdf186/fpdf.php";
include "../../../conexion.php";
$mysqli = conectar();

// Consulta SQL para obtener los datos
$query = "SELECT arregloGrafico, producto, maquina, ls, li, codigo, caducidad, fecha, hora, operador, supervisor
FROM control_estadistico_envasado
ORDER BY fecha DESC, hora DESC LIMIT 1";

$result = $mysqli->query($query);

// Array de valores a comparar (valores a la izquierda)
if ($result->num_rows > 0) {
    // Obtener los datos
    $row = $result->fetch_assoc();

    $producto = $row["producto"];
    $maquina = $row["maquina"];
    $ls = $row["ls"]; // Límite superior
    $li = $row["li"]; // Límite inferior
    $codigo = $row["codigo"];
    $caducidad = $row["caducidad"];
    $fecha = $row["fecha"];
    $arregloGrafico = $row["arregloGrafico"]; // El JSON
} else {
    die("No se encontraron resultados");
}

// Crear instancia del pdf
$pdf = new FPDF("L"); // Orientación de la hoja

$pdf->SetFillColor(200, 185, 255); // RGB

// Establece la conexión a la base de datos

// Decodificar el JSON y verificar si es válido
$grafico_data = json_decode($arregloGrafico, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error en la decodificación del JSON: " . json_last_error_msg());
}

// Array para guardar las posiciones de los puntos (asteriscos) entre páginas
$line_positions = [];
$asteriscos = [];
$previous_position = null;
// Cantidad de horas por página
$horas_por_pagina = 20;
$horas = [];

// Función para sombrear celdas
function shadedCell($pdf, $width, $height, $text, $border, $align, $fillColor)
{
    list($r, $g, $b) = $fillColor;
    $pdf->SetFillColor($r, $g, $b);
    $pdf->Cell($width, $height, $text, $border, 0, $align, true);
    $pdf->SetFillColor(255, 255, 255); // Restablecer el color de relleno a blanco
}

// Decodificación y extracción de las horas desde el JSON
foreach ($grafico_data as $entry) {
    $hora = isset($entry["hora"]) ? $entry["hora"] : "N/A";
    if (!in_array($hora, $horas)) {
        $horas[] = $hora;
    }
}

// Dividir las horas en bloques de 20
$total_horas = count($horas);
$paginas = ceil($total_horas / $horas_por_pagina); // Calcula cuántas páginas se necesitan

for ($pagina_actual = 0; $pagina_actual < $paginas; $pagina_actual++) {
    $pdf->AddPage();

    $pdf->SetY(6);
    $imagePath = "../../../img/logoaguida.jpg";
    $pdf->Image($imagePath, 20, 12, 35, 0, "JPEG");

    $pdf->SetFont("Arial", "", 6);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(45, 5, " ", 0, 0, "L");
    $pdf->Cell(180, 5, "", 0, 0, "C");
    $pdf->Cell(45, 5, "PF027", 0, 1, "R");

    $pdf->Ln(5);

    $pdf->Cell(90, 5, "ENVASADORA AGUIDA S.A. de C.V.", 0, 0, "R");

    $pdf->SetFont("Arial", "B", 10);

    $pdf->Cell(
        80,
        3,
        utf8_decode("Control Estadístico de Envasado"),
        0,
        1,
        "R"
    );
    $pdf->Ln(4);

    $pdf->SetFont("Arial", "B", 8);
    $pdf->Cell(10, 6, "", 0, 0, "R");
    $pdf->Cell(20, 6, "Producto: ", 0, 0, "R");
    $pdf->Cell(60, 6, $producto, "B", 0, "L");
    $pdf->Cell(20, 6, "", 0, 0, "L");

    $pdf->Cell(25, 6, utf8_decode("Contenido Neto:"), 0, 0, "R");
    $pdf->Cell(80, 6, utf8_decode(""), 0, 0, "L");

    $pdf->Cell(20, 6, "Fecha: ", 0, 0, "R");
    $pdf->Cell(30, 6, $fecha, "B", 1, "L");

    $pdf->Cell(10, 6, "", 0, 0, "R");

    $pdf->Cell(20, 6, utf8_decode("Máquina: "), 0, 0, "R");
    $pdf->Cell(60, 6, $maquina, "B", 0, "L");
    $pdf->Cell(20, 6, "", 0, 0, "L");

    $pdf->Cell(25, 6, utf8_decode("Tara de Envase:"), 0, 0, "R");
    $pdf->Cell(80, 6, utf8_decode(""), 0, 0, "L");

    // $pdf->Cell(10, 4, '', '', 0, 'L');
    $pdf->Cell(20, 6, utf8_decode("Código/Caducidad: "), 0, 0, "R");
    $pdf->Cell(30, 5, $caducidad . "/" . "$codigo", "B", 1, "L");

    $pdf->Ln(2);
    $pdf->Cell(10, 5, "", 0, 0, "R");
    $pdf->Cell(9, 5, "", 0, 0, "L");

    $pdf->Cell(80, 5, "", 0, 0, "L");
    $pdf->Cell(120, 5, "", "", 0, "L");
    $pdf->Ln(4);
    $pdf->Ln(2);
    $pdf->SetFont("Arial", "", 7);

    $pdf->Cell(28, 5, utf8_decode(""), 0, 0, "R");
    $pdf->Cell(18, 5, utf8_decode("L.I ="), 1, 0, "C", 1);
    $pdf->Cell(18, 5, utf8_decode($li), 1, 0, "C");
    $pdf->Cell(15, 5, utf8_decode(""), 0, 0, "C");
    $pdf->Cell(18, 5, utf8_decode("L.S ="), 1, 0, "C", 1);
    $pdf->Cell(18, 5, utf8_decode($ls), 1, 0, "C");
    $pdf->Cell(15, 5, utf8_decode(""), 0, 0, "C");
    $pdf->Cell(18, 5, utf8_decode("X ="), 1, 0, "C", 1);
    $xProm = ($li + $ls) / 2;
    $pdf->Cell(18, 5, utf8_decode($xProm), 1, 0, "C");
    $pdf->Cell(15, 5, utf8_decode(""), 0, 1, "C");

    $pdf->Ln(3);

    // Título de la tabla de horas
    // $pdf->Ln(0);
    $pdf->Cell(28, 5, "HORA: ", 0, 0, "R");

    // Obtener el bloque de horas correspondiente a esta página
    $inicio = $pagina_actual * $horas_por_pagina;
    $fin = min($inicio + $horas_por_pagina, $total_horas); // Asegura que no exceda el total de horas

    // Agregar las horas a la tabla del PDF
    for ($i = $inicio; $i < $fin; $i++) {
        $pdf->Cell(11.5, 5, $horas[$i] . "t", 1, 0, "C");
    }

    $valores_izquierda = [];
    $limite_inferior = $li;
    $limite_superior = $ls;
    $tolerancia = 0.2;

    // Crear el arreglo dinámico

    for ($valor = $limite_superior; $valor >= $limite_inferior; $valor -= 0.5) {
        $valores_izquierda[] = number_format($valor, 2, ".", "");

        // Generar filas con los valores directamente desde el JSON original
        for ($i = $inicio; $i < $fin; $i++) {
            // Obtener el valor del JSON original para esta fila
            $valor_json_actual = isset($grafico_data[$i]["valor"])
                ? number_format(floatval($grafico_data[$i]["valor"]), 2)
                : "0.00";
        }
    }
    // Mostrar el valor original


foreach ($valores_izquierda as $valor_izquierda) {
    // Dibujar el valor a la izquierda
    $pdf->Ln();

    $pdf->Cell(28, 5, $valor_izquierda . "p", 0, 0, "R");

    // Crear un array para identificar las posiciones de los valores de la hora
    $valor_pos = array_fill(0, count($horas), false);

    foreach ($grafico_data as $entry) {
        $hora_json = isset($entry["hora"]) ? $entry["hora"] : "";
        $valor_json = isset($entry["valor"]) ? $entry["valor"] : "";

        // Verificar los datos
        if ($valor_json == $valor_izquierda) {
            $pos = array_search($hora_json, $horas);
            if ($pos !== false) {
                $valor_pos[$pos] = true;
            }
        }
    

}






        $punto_generado = false; // Variable para controlar que solo se genere un punto por fila

        for ($j = $inicio; $j < $fin; $j++) {
            // Comparar directamente el valor actual del JSON
            $valor_grafico_actual = isset($grafico_data[$j]["valor"])
                ? floatval($grafico_data[$j]["valor"])
                : 0.0;

            // Verificar si el valor del JSON está dentro del margen
            if (
                !$punto_generado &&
                is_numeric($valor_grafico_actual) &&
                abs($valor_grafico_actual - floatval($valor_json_actual)) <=
                    $tolerancia
            ) {
                // Dibujar el punto y almacenar la posición
                $pdf->Cell(11.5, 5, chr(149) . "k", 1, 0, "C");
                $x = $pdf->GetX() - 5.75; // Ajuste del punto en la celda
                $y = $pdf->GetY() - 2.5; // Ajuste del punto en la celda
                $line_positions[$j] = ["x" => $x, "y" => $y];

                // Guardar los asteriscos con sus posiciones y valores
                $asteriscos[] = [
                    "hora" => $grafico_data[$j]["hora"],
                    "valor" => $grafico_data[$j]["valor"],
                    "x" => $x,
                    "y" => $y,
                ];
    // Imprimir los asteriscos en la celda correspondiente y almacenar sus posiciones
    $current_x = $pdf->GetX(); // X inicial de la fila
    for ($i = 0; $i < count($horas); $i++) {
        $cell_x = $current_x + ($i * 11.5); // Calcular la posición X de la celda
        $cell_y = $pdf->GetY(); // Y de la fila actual
  if ($valor_pos[$i]) {
            $pdf->Cell(11.5, 5, chr(149)."k", 1, 0, 'C'); // Código ASCII extendido para el símbolo de bullet

            // Almacenar la posición del asterisco con su coordenada
            $asteriscos[] = [
                'x' => $cell_x + 5.75, // Centro de la celda
                'y' => $cell_y + 2.5,  // Centro de la celda
                'hora' => $horas[$i],
                'valor' => $valor_izquierda
            ];
        } else {
            $pdf->Cell(11.5, 5, 'e', 1, 0, 'C');
        }
        }

    }


}}




    // Al final de cada página, asegúrate de guardar la posición anterior
    if ($previous_position !== null) {
        $line_positions[] = $previous_position;
    }
}


// Trazar líneas entre todos los asteriscos según el orden del JSON
for ($i = 1; $i < count($grafico_data); $i++) {
    $prev_entry = $grafico_data[$i - 1];
    $current_entry = $grafico_data[$i];

    // Trazar línea solo si ambas entradas tienen los valores necesarios
    if (
        isset(
            $prev_entry["hora"],
            $prev_entry["valor"],
            $current_entry["hora"],
            $current_entry["valor"]
        )
    ) {
        $prev_asterisco = array_filter($asteriscos, function ($a) use (
            $prev_entry
        ) {
            return $a["hora"] === $prev_entry["hora"] &&
                $a["valor"] === $prev_entry["valor"];
        });

        $current_asterisco = array_filter($asteriscos, function ($a) use (
            $current_entry
        ) {
            return $a["hora"] === $current_entry["hora"] &&
                $a["valor"] === $current_entry["valor"];
        });

        if (!empty($prev_asterisco) && !empty($current_asterisco)) {
            $prev_asterisco = reset($prev_asterisco);
            $current_asterisco = reset($current_asterisco);

            // Trazar la línea entre los puntos correctos
            $pdf->Line(
                $prev_asterisco["x"],
                $prev_asterisco["y"],
                $current_asterisco["x"],
                $current_asterisco["y"]
            );
        }
    }
}
$pdf->Ln(50);
$pdf->Cell(10, 4, "", 0, 0, "R");
$pdf->Cell(20, 4, "Operador:", 0, 0, "R");
$pdf->Cell(40, 4, "", "B", 0, "L");
$pdf->Cell(10, 4, "", 0, 0, "R");
$pdf->Cell(87, 4, "", "", 0, "L");
$pdf->Cell(20, 4, "Fecha", 0, 0, "R");
$pdf->Cell(25, 4, "", "B", 1, "L");

$pdf->Output("", "I");
