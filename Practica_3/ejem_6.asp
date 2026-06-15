<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo simple 6</title>
</head>
<body>
    <% mes = Month(Date)
    If (mes=7) Or (mes=8) Then
        Texto= "Aquí en México, donde está el equipo servidor, hace mucho calor en verano"
    Else
        Texto= "¡Qué importa el calor cuando tenemos vacaciones!"
    End If %>
    <p><% = Texto %></p>
</body>
</html>