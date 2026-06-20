<%@ LANGUAGE="JScript" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insertar un registro</title>
</head>
<body>
<%
if (Request.Form("DNI").Count > 0) {
    var Ob_Conn = Server.CreateObject("ADODB.Connection");
    var Ob_RS = Server.CreateObject("ADODB.Recordset");

    Ob_Conn.Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source=C:\\inetpub\\wwwroot\\App_web\\Practica_6\\Base_Datos\\Alumnos.accdb;Persist Security Info=False;");

    Ob_RS.Open("SELECT * FROM Datos_Alumnos", Ob_Conn, 1, 3);

    Ob_RS.AddNew();
    Ob_RS("DNI").Value = Request.Form("DNI");
    Ob_RS("Nombre").Value = Request.Form("Nombre");
    Ob_RS("Apellidos").Value = Request.Form("Apellidos");
    Ob_RS("Direccion").Value = Request.Form("Direccion");
    Ob_RS("Telefono").Value = Request.Form("Telefono");
    Ob_RS.Update();

    Ob_RS.Close();
    Ob_Conn.Close();
    
    Response.Write("<h3>Datos insertados correctamente</h3>");
    Response.Write("<a href=ejem_9.asp>Volver</a>");
} else {
%>
    <h3>ESCRIBA SUS DATOS PERSONALES</h3>
    <br>
    <form method="Post" action="ejem_9.asp">
        DNI: <input name="DNI" size="10"><br>
        NOMBRE: <input name="Nombre" size="15"><br>
        APELLIDOS: <input name="Apellidos" size="30"><br>
        DIRECCION: <input name="Direccion" size="30"><br>
        TELEFONO: <input name="Telefono" size="15"><br>
        <input type="Submit" value="Enviar datos">
        <input type="Reset" value="Borrar">
    </form>
<% 
} 
%>
</body>
</html>