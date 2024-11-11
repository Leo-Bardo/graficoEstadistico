<?php
include("../login/sesion.php");
include("../conexion.php");

$con = conectar();
$sql = "SELECT * FROM control_estadistico_envasado ORDER BY estado DESC";
$resultado = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Control Estadístico de Envasado</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <style>
        /* Estilos para el contenedor de la tabla */
        .table-container {
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            margin: 20px auto;
        }

        /* Estilos de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #04aa6d;
            color: white;
            border-radius: 5px;
        }

        td {
            background-color: #fff;
        }

        /* Estilos del cuadro de búsqueda */
        .search-box {
            width: 50%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
<iframe src="../encabezado.html" class="miClaseIframe"></iframe>
    <h1>Historial Control Estadístico de Envasado</h1>
    
    <div class="table-container">
        <input type="text" id="searchInput" class="search-box" placeholder="Buscar por ID..." onkeyup="searchTable()">
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Maquina</th>
                    <th>Código</th>
                    <th>Caducidad</th>
                    <th>LI</th>
                    <th>LS</th>
                    <th>Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $fila["idControlEstEnv"] . "</td>";
                        echo "<td>" . $fila["producto"] . "</td>";
                        echo "<td>" . $fila["maquina"] . "</td>";
                        echo "<td>" . $fila["codigo"] . "</td>";
                        echo "<td>" . $fila["caducidad"] . "</td>";
                        echo "<td>" . $fila["li"] . "</td>";
                        echo "<td>" . $fila["ls"] . "</td>";
                        echo "<td>" . $fila["hora"] . "</td>";
                        if ($fila["estado"] == 1) {
                            echo "<td><a href='editar.php?idProducto=" . $fila["idControlEstEnv"] . "'>Editar</a></td>";
                        } else {
                            echo "<td></td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No hay registros disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Función para buscar en la tabla por ID
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("tableBody");
            const rows = table.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const idCell = rows[i].getElementsByTagName("td")[0];
                if (idCell) {
                    const txtValue = idCell.textContent || idCell.innerText;
                    rows[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>
