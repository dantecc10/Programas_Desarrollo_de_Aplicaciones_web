<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar alumno con confirmacion</title>
</head>
<body>
    <h2>Eliminar alumno por DNI</h2>
    <form method="post" action="ejem_12.asp">
        DNI del alumno a eliminar: <input type="text" name="txtDNI">
        <input type="submit" value="Eliminar" onclick="return confirm('¿Esta seguro de eliminar este alumno?');">
    </form>
<%
    If Request.ServerVariables("REQUEST_METHOD") = "POST" Then
        Dim Obj_Conn, SQL, dni

        dni = Request.Form("txtDNI")

        If dni <> "" Then
            Set Obj_Conn = Server.CreateObject("ADODB.Connection")
            Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

            SQL = "DELETE FROM Datos_Alumnos WHERE DNI = " & dni
            Obj_Conn.Execute(SQL)

            Response.Write "<p style='color:green;'>Alumno con DNI " & dni & " eliminado (si existia).</p>"

            Obj_Conn.Close
            Set Obj_Conn = Nothing
        Else
            Response.Write "<p style='color:red;'>Debe indicar un DNI.</p>"
        End If
    End If
%>
<br><a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>