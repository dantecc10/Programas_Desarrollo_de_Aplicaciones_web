<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar alumno (UPDATE)</title>
</head>
<body>
<%
    Dim Obj_Conn, SQL

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "UPDATE Datos_Alumnos SET Apellido = 'Martinez' WHERE DNI = 123840"
    Obj_Conn.Execute(SQL)

    Response.Write "<h3>Alumno actualizado correctamente.</h3>"
    Response.Write "<a href='ejem_1.asp'>Ver listado completo</a>"

    Obj_Conn.Close
    Set Obj_Conn = Nothing
%>
</body>
</html>