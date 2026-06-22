<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar alumno por DNI</title>
</head>
<body>
<%
    Dim Obj_Conn, Obj_RS, SQL, dniBuscar

    dniBuscar = "459202"

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS = Server.CreateObject("ADODB.RecordSet")

    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "SELECT * FROM Datos_Alumnos WHERE DNI = " & dniBuscar
    Obj_RS.Open SQL, Obj_Conn, 3, 3

    If Obj_RS.EOF Then
        Response.Write "<h3>No se encontro alumno con DNI: " & dniBuscar & "</h3>"
    Else
        Response.Write "<h3>Alumno encontrado:</h3>"
        Response.Write "<table border='1'>"
        Response.Write "<tr><th>Nombre</th><th>Apellido</th><th>DNI</th></tr>"
        Do While Not Obj_RS.EOF
            Response.Write "<tr>"
            Response.Write "<td>" & Obj_RS("Nombre") & "</td>"
            Response.Write "<td>" & Obj_RS("Apellido") & "</td>"
            Response.Write "<td>" & Obj_RS("DNI") & "</td>"
            Response.Write "</tr>"
            Obj_RS.MoveNext
        Loop
        Response.Write "</table>"
    End If

    Obj_RS.Close
    Obj_Conn.Close
    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>
<br>
<a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>