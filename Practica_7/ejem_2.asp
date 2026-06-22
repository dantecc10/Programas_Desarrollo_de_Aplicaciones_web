<%@ LANGUAGE=JScript %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejemplo ASP con ADO y JScript</title>
</head>
<body>
<%
    var Ob_Conn = new ActiveXObject("ADODB.Connection");
    var Ob_RS = new ActiveXObject("ADODB.RecordSet");

    var ruta = Server.MapPath("Base_Datos/Alumnos.accdb");
    Ob_Conn.Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" + ruta);

    var Sql = "SELECT * FROM Datos_Alumnos";
    Ob_RS.Open(Sql, Ob_Conn, 3, 3);
%>

<center>
<table border="1">
    <tr>
        <th>D.N.I.</th>
        <th>Nombre</th>
        <th>Apellido</th>
    </tr>

<%
    while (!Ob_RS.Eof) {
%>
    <tr>
        <td><%= Ob_RS("DNI") %></td>
        <td><%= Ob_RS("Nombre") %></td>
        <td><%= Ob_RS("Apellido") %></td>
    </tr>
<%
        Ob_RS.MoveNext();
    }

    Ob_RS.Close();
    Ob_Conn.Close();
%>
</table>
</center>

</body>
</html>
