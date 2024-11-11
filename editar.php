<?php
include("../login/sesion.php");
include("../conexion.php");

$con = conectar();

$idProducto = isset($_GET['idProducto']) ? $_GET['idProducto'] : null;
$productoData = null;
if ($idProducto) {
    $sqlProducto = "SELECT producto, maquina AS envasadora, codigo, caducidad, li, ls, arregloGrafico FROM control_estadistico_envasado WHERE idControlEstEnv = ?";
    $stmt = $con->prepare($sqlProducto);
    $stmt->bind_param("i", $idProducto);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productoData = $resultado->fetch_assoc();
}

$arregloGrafico = isset($productoData['arregloGrafico']) ? json_decode($productoData['arregloGrafico'], true) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Estadístico de Envasado</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
</head>
<body>
    <iframe src="../encabezado.html" class="miClaseIframe"></iframe>

    <form action="procesarEditado.php" method="POST" accept-charset="utf-8" id="formularioControl">
        <div class="container">
            <h1 style="text-align: center;">CONTROL ESTADÍSTICO DE ENVASADO</h1>

            <?php
                $fechaHoy = date('Y-m-d');
                $hora = date('H:i:s');
                echo "<h5>" . $logUsuario . $fechaHoy . " " . $hora;
            ?>
            <br><br>

            <div class="form-container">
                <div class="column">
                    <label for="producto">PRODUCTO:</label>
                    <br>
                    <input name="producto" class="producto" id="productoSelect" value="<?php echo isset($productoData['producto']) ? $productoData['producto'] : ''; ?>">
                    <br><br>
                    <label for="envasadora">ENVASADORA:</label>
                    <br>
                    <input name="envasadora" class="envasadora" id="envasadoraSelect" value="<?php echo isset($productoData['envasadora']) ? $productoData['envasadora'] : ''; ?>">
                    <br><br>
                    <label>CÓDIGO: </label>
                    <br>
                    <input type="text" name="codigo" value="<?php echo isset($productoData['codigo']) ? $productoData['codigo'] : ''; ?>">
                    <br><br>
                    <label>CADUCIDAD:</label>
                    <br>
                    <input type="text" name="caducidad" value="<?php echo isset($productoData['caducidad']) ? $productoData['caducidad'] : ''; ?>">
                    <br><br>
                    <label for="li">LI: </label>
                    <br>
                    <input type="number" name="li" value="<?php echo isset($productoData['li']) ? $productoData['li'] : ''; ?>">
                    <br><br>
                    <label for="ls">LS: </label>
                    <br>
                    <input type="number" name="ls" value="<?php echo isset($productoData['ls']) ? $productoData['ls'] : ''; ?>">
                    <br><br>
                </div>

                <div class="column">
                    <label for="real-time">Hora:</label>
                    <input class="Hora" type="time" id="real-time" placeholder="Hora">
                    
                    <label for="valor">Valor:</label>
                    <input class="Valor" type="number" step=".50" id="valor" value="" placeholder="Valor">
                    <br><br>
                    <button type="button" id="guardarBtn">Agregar</button>

                    <h2>Valores Ingresados</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Valor</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaValores">
                            <?php
                            foreach ($arregloGrafico as $item) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($item['hora']) . "</td>
                                        <td>" . htmlspecialchars($item['valor']) . "</td>
                                        <td><button type='button' class='eliminarBtn'>Eliminar</button></td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br><br>
            <input type="hidden" name="arregloGrafico" id="arregloGraficoInput">
            <button type="submit" id="enviarBtn">FINALIZAR</button>
            <button id="salirBtn" onclick="location.href='../index.php'">SALIR</button>
        </div>
    </form>

    <script>
    const cuerpoTabla = document.getElementById('cuerpoTablaValores');
    const arregloGraficoInput = document.getElementById('arregloGraficoInput');

    function agregarFila(hora, valor) {
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>${hora}</td>
            <td>${valor}</td>
            <td><button type="button" class="eliminarBtn">Eliminar</button></td>
        `;
        nuevaFila.querySelector('.eliminarBtn').addEventListener('click', function() {
            cuerpoTabla.removeChild(nuevaFila);
        });
        cuerpoTabla.appendChild(nuevaFila);
        
        // Ordenar la tabla después de agregar una nueva fila
        ordenarTablaPorHora();
    }

    function ordenarTablaPorHora() {
        const filas = Array.from(cuerpoTabla.rows);
        filas.sort((a, b) => {
            const horaA = a.cells[0].innerText;
            const horaB = b.cells[0].innerText;
            return horaA.localeCompare(horaB);0
        });
        filas.forEach(fila => cuerpoTabla.appendChild(fila));
    }

    function recogerDatosTabla() {
        const filas = cuerpoTabla.querySelectorAll('tr');
        const arregloGrafico = Array.from(filas).map(fila => {
            const hora = fila.cells[0].innerText;
            const valor = fila.cells[1].innerText;
            return { hora, valor };
        });
        arregloGraficoInput.value = JSON.stringify(arregloGrafico);
    }

    document.getElementById('guardarBtn').addEventListener('click', function() {
        const horaInput = document.getElementById('real-time').value;
        const valorInput = document.getElementById('valor').value;

        if (horaInput && valorInput) {
            agregarFila(horaInput, valorInput);
            document.getElementById('real-time').value = '';
            document.getElementById('valor').value = '';
        } else {
            alert('Por favor, complete ambos campos.');
        }
    });

    document.getElementById('formularioControl').addEventListener('submit', function(e) {
        recogerDatosTabla();
    });
</script>

</body>
</html>









