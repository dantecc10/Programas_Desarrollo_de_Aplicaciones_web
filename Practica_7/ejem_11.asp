<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar alumno por DNI</title>
</head>
<body>
    <h2>Actualizar datos de un alumno</h2>
    <form method="post" action="ejem_11.asp">
        DNI del alumno: <input type="text" name="txtDNI"><br>
        Nuevo nombre: <input type="text" name="txtNombre"><br>
        Nuevo apellido: <input type="text" name="txtApellido"><br>
        <input type="submit" value="Actualizar">
    </form>
<%
    If Request.ServerVariables("REQUEST_METHOD") = "POST" Then
        Dim Obj_Conn, SQL, dni, nombre, apellido

        dni = Request.Form("txtDNI")
        nombre = Request.Form("txtNombre")
        apellido = Request.Form("txtApellido")

        If dni <> "" And (nombre <> "" Or apellido <> "") Then
            Set Obj_Conn = Server.CreateObject("ADODB.Connection")
            Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

            If nombre <> "" Then
                SQL = "UPDATE Datos_Alumnos SET Nombre = '" & nombre & "' WHERE DNI = " & dni
                Obj_Conn.Execute(SQL)
            End If

            If apellido <> "" Then
                SQL = "UPDATE Datos_Alumnos SET Apellido = '" & apellido & "' WHERE DNI = " & dni
                Obj_Conn.Execute(SQL)
            End If

            Response.Write "<p style='color:green;'>Alumno actualizado correctamente.</p>"

            Obj_Conn.Close
            Set Obj_Conn = Nothing
        Else
            Response.Write "<p style='color:red;'>Debe indicar el DNI y al menos un campo a actualizar.</p>"
        End If
    End If
%>
<br><a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>