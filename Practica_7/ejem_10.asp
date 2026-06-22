<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Busqueda por letra (LIKE)</title>
</head>
<body>
    <h2>Buscar alumnos cuyo nombre comienza con una letra</h2>
    <form method="get" action="ejem_10.asp">
        Letra: <input type="text" name="letra" maxlength="1" size="1">
        <input type="submit" value="Buscar">
    </form>
<%
    Dim Obj_Conn, Obj_RS, SQL, letra

    letra = Request.QueryString("letra")

    If letra <> "" Then
        Set Obj_Conn = Server.CreateObject("ADODB.Connection")
        Set Obj_RS = Server.CreateObject("ADODB.RecordSet")

        Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

        SQL = "SELECT * FROM Datos_Alumnos WHERE Nombre LIKE '" & letra & "%'"
        Obj_RS.Open SQL, Obj_Conn, 3, 3

        If Not Obj_RS.EOF Then
%>
            <table border="1">
                <tr><th>Nombre</th><th>Apellido</th><th>DNI</th></tr>
<%          Do While Not Obj_RS.EOF %>
                <tr>
                    <td><%= Obj_RS("Nombre") %></td>
                    <td><%= Obj_RS("Apellido") %></td>
                    <td><%= Obj_RS("DNI") %></td>
                </tr>
<%              Obj_RS.MoveNext
            Loop %>
            </table>
<%      Else
            Response.Write "<p>No se encontraron alumnos con esa letra.</p>"
        End If

        Obj_RS.Close
        Obj_Conn.Close
        Set Obj_RS = Nothing
        Set Obj_Conn = Nothing
    End If
%>
<br><a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>