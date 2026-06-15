<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo simple 7</title>
</head>
<body>
    Tabla de multiplicar sin cabeceras<BR> <BR>
    <TABLE BORDER="1" WIDTH="70">
    <% for i=l to 10 %>
        <TR>
        <% for j=1 to 10 %>
            <TD> <% = j*i %> </TD>
        <% next %>
        </TR>
    <% next %>
    </TABLE>
</body>
</html>