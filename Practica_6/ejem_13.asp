<%@ LANGUAGE="JScript" %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar un registro</title>
</head>
<body>
<%
if (Request.Form("DNI").Count > 0) {
    var Ob_Conn = Server.CreateObject("ADODB.Connection");
    var Ob_RS   = Server.CreateObject("ADODB.Recordset");

    // Conexión directa al archivo ACCDB
    Ob_Conn.Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source=C:\\inetpub\\wwwroot\\App_web\\Practica_6\\Base_Datos\\Alumnos.accdb;Persist Security Info=False;");

    var sql = "SELECT * FROM Datos_Alumnos WHERE DNI=" + Request.Form("DNI");

    Ob_RS.Open(sql, Ob_Conn, 1, 3); // 1 = adOpenKeyset, 3 = adLockOptimistic

    if (!Ob_RS.EOF) {
        Ob_RS.Delete();
        Response.Write("<h3>Dato borrado</h3>");
    } else {
        Response.Write("<h3>No se encontró el usuario con ese DNI</h3>");
    }

    Ob_RS.Close();
    Ob_Conn.Close();
} else {
%>
    <h3>ESCRIBA EL D.N.I. DEL USUARIO A BORRAR</h3><br>
    <form method="Post" action="ejem_13.asp">
        DNI: <input name="DNI" size="10"><br><br>
        <input type="Submit" value="Enviar datos">
        <input type="Reset" value="Borrar">
    </form>
<% } %>
</body>
</html>
