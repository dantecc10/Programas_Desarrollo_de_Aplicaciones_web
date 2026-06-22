<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar alumno (INSERT)</title>
</head>
<body>
<%
    Dim Obj_Conn, SQL

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "INSERT INTO Datos_Alumnos (Nombre, Apellido, DNI) VALUES ('Carlos', 'Lopez', 558877)"
    Obj_Conn.Execute(SQL)

    Response.Write "<h3>Alumno insertado correctamente.</h3>"
    Response.Write "<a href='ejem_1.asp'>Ver listado completo</a>"

    Obj_Conn.Close
    Set Obj_Conn = Nothing
%>
</body>
</html>