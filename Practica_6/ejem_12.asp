<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar un alumno de la base de datos</title>
</head>
<body>
<%
If Request.Form("DNI") = "" Then
%>
    <form method="Post" action="ejem_12.asp">
        <h2>Inserte el DNI del alumno que desee borrar</h2>
        DNI: <input name="DNI"><br><br>
        <input type="Submit" value="Enviar">
    </form>
<%
Else
    ' Declaración de variables
    Dim Obj_Conn, Obj_RS, DNI
    DNI = Request.Form("DNI")

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS   = Server.CreateObject("ADODB.Recordset")

    ' Conexión directa al archivo ACCDB
    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=C:\inetpub\wwwroot\App_web\Practica_6\Base_Datos\Alumnos.accdb;Persist Security Info=False;"

    ' Abrimos el registro con ese DNI
    ' Si el campo DNI es texto en Access, usa comillas:
    ' "SELECT * FROM Datos_Alumnos WHERE DNI='" & DNI & "'"
    Obj_RS.Open "SELECT * FROM Datos_Alumnos WHERE DNI=" & DNI, Obj_Conn, 1, 3

    If Not Obj_RS.EOF Then
        Obj_RS.Delete
        Response.Write("<center><h1>DATOS ELIMINADOS</h1></center>")
    Else
        Response.Write("<center><h1>No se encontró el alumno con ese DNI</h1></center>")
    End If

    Obj_RS.Close
    Obj_Conn.Close
    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
End If
%>
</body>
</html>
