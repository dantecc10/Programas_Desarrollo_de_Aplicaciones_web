<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar alumno (DELETE)</title>
</head>
<body>
<%
    Dim Obj_Conn, SQL

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "DELETE FROM Datos_Alumnos WHERE DNI = 558877"
    Obj_Conn.Execute(SQL)

    Response.Write "<h3>Alumno eliminado correctamente (si existia).</h3>"
    Response.Write "<a href='ejem_1.asp'>Ver listado completo</a>"

    Obj_Conn.Close
    Set Obj_Conn = Nothing
%>
</body>
</html>