<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar los datos de un alumno</title>
</head>
<body>
<%
If Request.Form("DNI") = "" Then
%>
    <form method="Post" action="ejem_10.asp">
        <h2>Inserte el DNI del alumno que desee actualizar y los nuevos datos</h2>
        DNI: <input name="DNI"><br>
        Nombre: <input name="Nombre"><br>
        Apellidos: <input name="Apellidos"><br>
        Dirección: <input name="Direccion"><br>
        Teléfono: <input name="Telefono"><br>
        <input type="Submit" value="Enviar">
    </form>
<%
Else
    ' Declaración de variables
    Dim Obj_Conn, Obj_RS
    Dim Nombre, Apellidos, DNI, Direccion, Telefono

    Nombre    = Request.Form("Nombre")
    Apellidos = Request.Form("Apellidos")
    DNI       = Request.Form("DNI")
    Direccion = Request.Form("Direccion")
    Telefono  = Request.Form("Telefono")

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS   = Server.CreateObject("ADODB.Recordset")

    ' Conexión directa al archivo ACCDB
    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=C:\inetpub\wwwroot\App_web\Practica_6\Base_Datos\Alumnos.accdb;Persist Security Info=False;"

    ' Abrimos el registro del alumno con ese DNI
    Obj_RS.Open "SELECT * FROM Datos_Alumnos WHERE DNI=" & DNI, Obj_Conn, 1, 3

    If Not Obj_RS.EOF Then
        Obj_RS("Nombre")    = Nombre
        Obj_RS("Apellidos") = Apellidos
        Obj_RS("Direccion") = Direccion
        Obj_RS("Telefono")  = Telefono
        Obj_RS.Update
    End If

    Obj_RS.Close
    Obj_Conn.Close

    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>
    <center><h1>DATOS ACTUALIZADOS</h1></center>
<%
End If
%>
</body>
</html>
