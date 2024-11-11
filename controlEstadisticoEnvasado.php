<?php
include("conexion.php");

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
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
 <style>
        .draggable {
            cursor: move;
        }
        .draggable:hover {
            background-color: #f0f0f0;
        }
    </style>

<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <br><br><div class="container"><br>
        <h1 style="text-align: center;">CONTROL ESTADISTICO DE ENVASADO</h1>

        <br><br>

        <div class="form-container">
            <div class="column">
                <label for="producto">PRODUCTO:</label>
                <br><br>
                <input type="text" id="searchProducto" class="producto" placeholder="Buscar producto...">
                <br><br>
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
                <label for="envasadora">ENVASADORA:</label>
                <br>
                <select name="envasadora" class="envasadora" id="envasadoraSelect" autofocus>
                    <option value="">Selecciona Envasadora</option>
                    <?php
                    if ($resultadoEquipo->num_rows > 0) {
                        while ($fila = $resultadoEquipo->fetch_assoc()) {
                            echo "<option value='" . $fila["idEquipo"] . "'>" . $fila["equipo"] . "</option>";
                        }
                    }
                    ?>
                </select>
                <br><br>
                <label>CÓDIGO: </label>
                <br>
                <input type="text" name="codigo" value="">
                <br><br>
                <label>CADUCIDAD: 
                <br>  
                <input type="date" name="caducidad"  value="<?php echo date('Y-m-d', strtotime('+1 year'));?>"></label>
                <br><br>
                <label for="li">LI: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="li" id="li" oninput="actualizarLSyPromedio()">
                <br><br>
                <label for="ls">LS: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="ls" id="ls" readonly>
                <br><br>

                <label for="x">X: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="x" id="x" readonly>
                <br><br>
            </div>

            <!-- Segunda columna -->
            <div class="column">
                <label for="real-time">Hora:</label>
                <input class="Hora" type="time" id="real-time" placeholder="Hora" required>
                
                <label for="valor">Valor:</label>
                <input class="Valor" type="number" step=".50" id="valor" value="" placeholder="Valor" required oninput="validarValor()">
                <br><br>

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
            </div>
        </div>

        <!-- Botones Finalizar y Salir -->
         <br><br>
        <button id="enviarBtn">FINALIZAR</button>
        <button id="salirBtn" onclick="location.href='menuValidacionCEE.php'">VOLVER A MENÚ</button>

    <script>
let valControlEstadistico = [];

document.getElementById('guardarBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario
    const hora = document.getElementById('real-time').value;
    const valor = document.getElementById('valor').value;

    if (hora && valor) {
                const tabla = document.getElementById('cuerpoTablaValores');
                const nuevaFila = document.createElement('tr');
                nuevaFila.classList.add('draggable'); // Agregar clase para arrastre
                nuevaFila.setAttribute('draggable', true); // Habilitar arrastre
                nuevaFila.innerHTML = `
                    <td>${hora}</td>
                    <td>${valor}</td>
                    <td><button class="eliminarBtn">Eliminar</button></td>
                `;
                // Insertar la nueva fila al principio
                tabla.insertBefore(nuevaFila, tabla.firstChild);

                // Agregar los valores al array
                valControlEstadistico.push({ hora: hora, valor: valor });

                // Hacer que las filas sean arrastrables
                hacerFilasArrastrables(nuevaFila);

                // Actualizar el promedio al añadir un nuevo valor
                actualizarPromedio();
            }
        });


        document.getElementById('enviarBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario

    // Verificar si las horas están ordenadas
    if (!verificarHorasOrdenadas()) {
        Swal.fire('Error', 'Las horas deben estar ordenadas de forma descendente.', 'error');
        return; // No continuar con el envío si hay error
    }

    const data = {
        producto: document.querySelector('select[name="producto"]').value,
        envasadora: document.querySelector('select[name="envasadora"]').value,
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success');
            // Limpiar los campos si es necesario
            document.getElementById('cuerpoTablaValores').innerHTML = ''; // Limpiar la tabla
            valControlEstadistico = []; // Resetear el array
        } else {
            Swal.fire('Error', data.message || 'No se pudo completar la operación', 'error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al enviar los datos: ' + error.message, 'error');
    });
});

// Función para verificar que las horas están ordenadas de forma descendente
function verificarHorasOrdenadas() {
    const filas = document.querySelectorAll('#cuerpoTablaValores tr');
    let horas = [];

    filas.forEach(fila => {
        const horaCell = fila.cells[0].textContent; // Suponiendo que la hora está en la primera celda
        horas.push(horaCell);
    });

    // Verificar si las horas están en orden descendente
    for (let i = 0; i < horas.length - 1; i++) {
        if (horas[i] < horas[i + 1]) { // Si la hora actual es menor que la siguiente
            return false; // No están ordenadas
        }
    }
    return true; // Están ordenadas
}


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


 function setCurrentTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0'); // Asegura que tenga dos dígitos
        const minutes = String(now.getMinutes()).padStart(2, '0'); // Asegura que tenga dos dígitos
        document.getElementById('real-time').value = `${hours}:${minutes}`; // Establece el valor
    }

    // Llama a la función al cargar la página
    window.onload = setCurrentTime;

    function actualizarLSyPromedio() {
        // Obtener el valor de LI
        const li = parseFloat(document.getElementById('li').value);

        // Si LI tiene un valor numérico válido
        if (!isNaN(li)) {
            // Calcular LS sumando 10 a LI
            const ls = li + 10;
            document.getElementById('ls').value = ls.toFixed(2); // Actualizar el campo LS con dos decimales

            // Calcular el promedio de LI y LS
            const promedio = (li + ls) / 2;
            document.getElementById('x').value = promedio.toFixed(2); // Actualizar el campo X con dos decimales

            // Colocar el promedio en el campo "Valor", si el usuario no lo ha modificado
            const valorInput = document.getElementById('valor');
            if (valorInput.dataset.modificado !== "true") {
                valorInput.value = promedio.toFixed(2); // Establecer el valor inicial del campo
            }
        }
    }

    function validarValor() {
        const li = parseFloat(document.getElementById('li').value);
        const ls = parseFloat(document.getElementById('ls').value);
        const valorInput = document.getElementById('valor');
        const valor = parseFloat(valorInput.value);

        // Verificar que el valor no sea menor que LI - 10 ni mayor que LS + 10
        if (valor < (li - 10) || valor > (ls + 10)) {
            alert(`El valor debe estar entre ${li - 10} y ${ls + 10}`);
            
            // Establecer el campo Valor al promedio calculado
            const promedio = (li + ls) / 2;
            valorInput.value = promedio.toFixed(2); // Restablecer el valor al promedio
        } else {
            valorInput.dataset.modificado = "true"; // Marcar que el usuario ha modificado el campo
        }
    }

    document.getElementById('valor').addEventListener('input', function() {
    // Reemplaza cualquier carácter no numérico excepto '.' y corrige el formato
    let value = this.value.replace(/[^0-9.]/g, '');
    
    // Si hay más de un punto, solo conserva el primero
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Formatear el número
    const parsedValue = parseFloat(value);
    if (!isNaN(parsedValue)) {
        this.value = parsedValue.toFixed(2);
    } else {
        this.value = '';
    }
});

// Función para hacer que las filas sean arrastrables
        function hacerFilasArrastrables(fila) {
            fila.querySelector('.eliminarBtn').addEventListener('click', function() {
                const index = Array.from(fila.parentNode.children).indexOf(fila);
                valControlEstadistico.splice(index, 1); // Eliminar del array
                fila.remove(); // Eliminar la fila de la tabla
                actualizarPromedio(); // Actualizar promedio al eliminar
            });

            // Eventos de arrastre
            fila.addEventListener('dragstart', function() {
                fila.classList.add('dragging');
            });

            fila.addEventListener('dragend', function() {
                fila.classList.remove('dragging');
            });

            fila.addEventListener('dragover', function(e) {
                e.preventDefault();
                const dragging = document.querySelector('.dragging');
                const afterElement = getDragAfterElement(fila.parentNode, e.clientY);
                if (afterElement == null) {
                    fila.parentNode.appendChild(dragging);
                } else {
                    fila.parentNode.insertBefore(dragging, afterElement);
                }
            });
        }

        // Obtener el elemento después del que se está arrastrando
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.draggable:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
</script>


</body>
</html>
