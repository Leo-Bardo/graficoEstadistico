<!-- PARA INGRESAR A ESETA INTERFAZ CREAR UN LOGEO Y PARA GRABAR OPERADOR Y GESTIONAR LA REVISIÓN DEL SUPERVISORS -->
<?php
include("../login/sesion.php");
include("../conexion.php");

$con = conectar();

$sql = "SELECT idProducto, producto FROM productos";
$resultadoProducto = $con->query($sql);

$sql = "SELECT idEquipo, equipo FROM equipos WHERE tipoEquipo = 99";
$resultadoEquipo = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Estadístico de Envasado</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<iframe src="../encabezado.html" class="miClaseIframe"></iframe><!-- considerar colocarlo por fuera para abrir desde logotipo Aguida -->
    <!-- Formulario principal -->
    <form action="procesarEstadistica.php" method="POST" accept-charset="utf-8">
        
        <br><br><div class="container"><br>
    <h1 style="text-align: center;">CONTROL ESTADISTICO DE ENVASADO</h1>
        <!-- REVISAR QUE LA FECHA IMPRESA EN LA INTERFAZ Y EN BD COINCIDAN -->
        <?php
            $fechaHoy = date('Y-m-d');
            $hora = date('H:i:s');
            echo $fechaHoy . " " . $hora;
        ?>
            <br>
            <label for="producto">PRODUCTO:</label>
            <input type="text" id="searchProducto" class="producto" placeholder="Buscar producto...">
            <select name="producto" class="producto" id="productoSelect" required autofocus>
                <option value="">Selecciona un producto</option>

                <?php
                if ($resultadoProducto->num_rows > 0) {
                    while ($fila = $resultadoProducto->fetch_assoc()) {
                        echo "<option value='" . $fila["idProducto"] . "'>" . $fila["producto"] . "</option>";
                    }
                }
                ?>
            </select>
<br><br>
<label for="envasadora">ENVASADORA:

            <select name="envasadora" class="envasadora" id="envasadoraSelect" required autofocus>
                <option value="">Selecciona Envasadora</option>

                <?php
                if ($resultadoEquipo->num_rows > 0) {
                    while ($fila = $resultadoEquipo->fetch_assoc()) {
                        echo "<option value='" . $fila["idEquipo"] . "'>" . $fila["equipo"] . "</option>";
                    }
                }
                ?>
            </select>

<!--    <select name="maquina">

        <option>A3 FLex Edge</option>
        <option>A3 FLex Prisma</option>
        <option>A3 Flex Compact</option>
        <option>A3 FLex</option>
        <option>TBA 8 Slim</option>
        <option>TBA 8 Base</option>
    </select> -->
<br><br>
<label>CÓDIGO: <input type="text" name="codigo" value=""></label>
<label>CADUCIDAD: <input type="date" name="caducidad"  value="<?php echo date('Y-m-d', strtotime('+1 year'));?>">
    <!-- obtengo la fecha con un año de adelanto -->
</label>
    <br><br>
    <label for="">LS: <input type="number" value="965.46" name="ls"></label>
    <br>
    <br>
    <label for="">LI: <input type="number" value="955.46" name="li"></label>
    <!-- X no se captura ya que es el resultado de la formula ((LI)(LS))/2  -->
    <label for="">X: <input type="text" value="((LI)(LS))/2" name=""></label>
    <br>
    <br>


    <!-- Inputs para agregar hora y valor -->
    <label for="real-time">Hora:</label>
    <input type="time" id="real-time" placeholder="Hora" required>

    <label for="valor">Valor:</label>
    <input type="number" id="valor" placeholder="Valor">

    <!-- Botón Guardar -->
    <button id="guardarBtn">Guardar</button>

    <!-- Tabla para mostrar los valores ingresados -->
    <h2>Valores Ingresados</h2>
    <table>
        <thead>
            <tr>
                <th>Hora</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody id="cuerpoTablaValores">
            <!-- Aquí se agregarán los valores dinámicamente -->
        </tbody>
    </table>

    <!-- Botón para enviar toda la información -->
    <button id="reporteBtn" onclick="habilitarReporte()">FINALIZAR</button>
<button id="enviarBtn" type="button" disabled onclick="location.href='../panelControl/menuValidacion/controlEstadistico/generarReporteEST.php'">GENERAR REPORTE</button>

    <!-- Asegúrate de incluir la librería SweetAlert -->

    <script>
let valControlEstadistico = [];

document.getElementById('guardarBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario
    const hora = document.getElementById('real-time').value;
    const valor = document.getElementById('valor').value;

    if (hora && valor) {
        // Agregar los valores a la tabla al inicio
        const tabla = document.getElementById('cuerpoTablaValores');
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>${hora}</td>
            <td>${valor}</td>
        `;
        // Insertar la nueva fila al principio
        tabla.insertBefore(nuevaFila, tabla.firstChild);

        // Agregar los valores al array
        valControlEstadistico.push({ hora: hora, valor: valor });
        console.log(valControlEstadistico);

        // Limpiar el campo de valor
        document.getElementById('valor').value = '';
    }
});


// Lógica para enviar datos a la base de datos
document.getElementById('enviarBtn').addEventListener('click', function() {
    const data = {
        producto: document.querySelector('select[name="producto"]').value,
        maquina: document.querySelector('select[name="maquina"]').value,
        ls: document.querySelector('input[name="ls"]').value,
        li: document.querySelector('input[name="li"]').value,
        codigo: document.querySelector('input[name="codigo"]').value,
        caducidad: document.querySelector('input[name="caducidad"]').value,
        valores: valControlEstadistico // Aquí se envían los valores
    };

    fetch('procesarEstadistica.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success');
            // Limpiar los campos si es necesario
            document.getElementById('cuerpoTablaValores').innerHTML = ''; // Limpiar la tabla
            valControlEstadistico = []; // Resetear el array
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al enviar los datos', 'error');
    });
});

// Función para buscar en el select
document.getElementById('searchProducto').addEventListener('keyup', function() {
        var searchText = this.value.toLowerCase();
        var select = document.getElementById('productoSelect');
        var options = select.options;

        for (var i = 0; i < options.length; i++) {
            var optionText = options[i].text.toLowerCase();
            if (optionText.includes(searchText)) {
                options[i].style.display = '';
            } else {
                options[i].style.display = 'none';
            }
        }
    });
// Se desabilita aunque no esten llenos los campos del formulario
function habilitarReporte() {
    // Habilitar el botón de generar reporte
    document.getElementById('reporteBtn').disabled = false;
}
</script>



</body>
</html>
