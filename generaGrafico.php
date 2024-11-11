<!DOCTYPE html>
<html lang="es">
<head>
    <title>Usando Highcharts</title>
    <meta charset="utf-8" />
    
    <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <br><br>
        <!-- Barra de búsqueda para hora o rango de horas -->
        <label for="horaInicio">Hora Inicio (24hrs):</label>
        <input type="text" id="horaInicio" placeholder="Ej. 08:00" />
        <label for="horaFin">Hora Fin (24hrs):</label>
        <input type="text" id="horaFin" placeholder="Ej. 18:00" />
        <button id="buscarBtn">Buscar</button>
        <br><br>
        <div id="grafica"></div>
        
        <script>
        $(document).ready(function() {
            var chart;

            function renderChart(categorias, valores, li, ls, promedio, producto, estado, maquina) {
                chart = Highcharts.chart('grafica', {
                    chart: {
                        zoomType: 'x',
                        panning: true,
                        panKey: 'shift',
                        type: 'line',
                        events: {
                            load: function() {
                                var chart = this;
                                chart.renderer.image('img/logoaguida.jpg', 70, 10, 95, 35).add();
                            }
                        }
                    },
                    title: { text: 'CONTROL ESTADÍSTICO DE ENVASADO' },
                    subtitle: {
                        text: `<div style="text-align: center; margin-top: 10px;">
                                    Límite Inferior (LI): <span style="color: blue;">${li.toFixed(2)}</span> | 
                                    Promedio: <span style="color: green;">${promedio.toFixed(2)}</span> | 
                                    Límite Superior (LS): <span style="color: red;">${ls.toFixed(2)}</span><br>
                                    Producto: <strong>${producto}</strong> | 
                                    Estado: <strong>${estado === 1 ? 'Activo' : 'Inactivo'}</strong> | 
                                    Máquina: <strong>${maquina}</strong> |
                                    CPK: <strong>2.0</strong>

                                </div>`,
                        useHTML: true
                    },
                    xAxis: {
                        categories: categorias,
                        title: {text: 'Horas'}
                    },
                    yAxis: {
                        title: {text: 'Porcentaje %'},
                        plotLines: [
                            { value: li, color: 'blue', dashStyle: 'ShortDash', width: 2, label: { text: 'LI (' + li.toFixed(2) + ')', align: 'left', style: {color: 'blue'} }},
                            { value: promedio, color: 'green', dashStyle: 'ShortDash', width: 2, label: { text: 'Prom (' + promedio.toFixed(2) + ')', align: 'left', style: {color: 'green'} }},
                            { value: ls, color: 'red', dashStyle: 'ShortDash', width: 2, label: { text: 'LS (' + ls.toFixed(2) + ')', align: 'left', style: {color: 'red'} }}
                        ]
                    },
                    tooltip: {valueSuffix: '%'},
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle',
                        borderWidth: 0
                    },
                    series: [{ name: 'Datos', data: valores }],
                    plotOptions: { line: { dataLabels: { enabled: true } }},
                    responsive: {
                        rules: [{
                            condition: { maxWidth: 600 },
                            chartOptions: {
                                legend: { align: 'center', verticalAlign: 'bottom', layout: 'horizontal' },
                                yAxis: { labels: {align: 'right'}, title: { text: null }},
                                subtitle: { text: null }
                            }
                        }]
                    }
                });
            }

            $.ajax({
                url: 'obtenerDatos.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var categorias = [];
                    var valores = [];
                    $.each(data.grafico_data, function(index, item) {
                        categorias.push(item.hora);
                        valores.push(parseFloat(item.valor));
                    });

                    var li = parseFloat(data.li);
                    var ls = parseFloat(data.ls);
                    var promedio = (li + ls) / 2;
                    var producto = data.producto;
                    var maquina = data.maquina;
                    var estado = data.estado;

                    renderChart(categorias, valores, li, ls, promedio, producto, estado, maquina);

                    $('#buscarBtn').click(function() {
                        var horaInicio = $('#horaInicio').val();
                        var horaFin = $('#horaFin').val();

                        if (!horaInicio || !horaFin) {
                            alert('Por favor, ingrese ambas horas en formato 24hrs.');
                            return;
                        }

                        var inicioIndex = categorias.indexOf(horaInicio);
                        var finIndex = categorias.indexOf(horaFin);

                        if (inicioIndex === -1 || finIndex === -1 || finIndex < inicioIndex || finIndex >= categorias.length) {
                            alert('Rango de horas no válido. Verifique las horas ingresadas.');
                            return;
                        }

                        var categoriasFiltradas = categorias.slice(inicioIndex, finIndex + 1);
                        var valoresFiltrados = valores.slice(inicioIndex, finIndex + 1);

                        chart.update({
                            xAxis: {categories: categoriasFiltradas},
                            series: [{data: valoresFiltrados}]
                        });

                        chart.xAxis[0].setExtremes(inicioIndex, finIndex, true, { duration: 1000 });
                    });
                },
                error: function(error) {
                    console.log("Error al obtener los datos: ", error);
                }
            });
        });
        </script>
    </div>
</body>
</html>
