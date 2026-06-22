<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de insercion (POST)</title>
</head>
<body>
    <h2>Insertar nuevo alumno</h2>
    <form method="post" action="ejem_8.asp">
        Nombre: <input type="text" name="txtNombre"><br>
        Apellido: <input type="text" name="txtApellido"><br>
        DNI: <input type="text" name="txtDNI"><br>
        <input type="submit" value="Insertar">
    </form>
<%
    If Request.ServerVariables("REQUEST_METHOD") = "POST" Then
        Dim Obj_Conn, SQL, nombre, apellido, dni

        nombre = Request.Form("txtNombre")
        apellido = Request.Form("txtApellido")
        dni = Request.Form("txtDNI")

        If nombre <> "" And apellido <> "" And dni <> "" Then
            Set Obj_Conn = Server.CreateObject("ADODB.Connection")
            Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

            SQL = "INSERT INTO Datos_Alumnos (Nombre, Apellido, DNI) VALUES ('" & nombre & "', '" & apellido & "', '" & dni & "')"
            Obj_Conn.Execute(SQL)

            Response.Write "<p style='color:green;'>Alumno insertado correctamente.</p>"

            Obj_Conn.Close
            Set Obj_Conn = Nothing
        Else
            Response.Write "<p style='color:red;'>Todos los campos son obligatorios.</p>"
        End If
    End If
%>
<br><a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>