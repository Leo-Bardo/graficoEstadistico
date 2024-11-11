# Instrucciones de configuración para pruebas de control estadistico de envasado
- Clonar el repositorio
- Crear una BBDDs con el nombre bdaguida
- Iniciar servidor XAMPP
- Correr proyecto en cmd - php -S 127.0.0.1:8080
- Abrir proyecto en navegador

Una vez abierto aparece el pdf que genera el grafico a partir del script php del archivo, existen otros dos scripts, prueba y pruebaJulio, en los cuales se puede encontrar codigo de configuración del script.

# Puntos a tomar en consideración:
- Correcta generación de grafico a partir del arreglo JSON que aparece de ejemplo desde la BBDD
- Obtención de registros correctos traidos desde la BBDD
- Salto de página automatico cada 20 valores del arreglo
- Tomar en cuenta el salto de página independiente del arreglo-hora:valor y los valores_izquierdos generados a partir de los limites.
- La imagen del reporte definitivo esta contenida en la raíz del proyecto bajo el nombre: 'pdfEstadistico.jpeg'

## Nota: El presente modulo fue extraido del proyecto softguida para el control de datos operativos en el área de producción con la finalidad de optimizar la generación del reporte de control estadistico de envasado.