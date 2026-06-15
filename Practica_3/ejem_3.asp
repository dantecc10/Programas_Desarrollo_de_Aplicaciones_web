<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo simple 3</title>
</head>
<body>
    <h3>La suma de los 100 primeros numeros enteros es: </h3>

    <% Acumulador = 0 
    for Indice = 1 to 100
    Acumulador = Acumulador + Indice 
    next %>
    <% = Acumulador %>
</body>
</html>