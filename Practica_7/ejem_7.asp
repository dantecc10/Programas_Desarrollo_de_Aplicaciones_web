<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado ordenado por nombre (ORDER BY)</title>
</head>
<body>
    <h2>Alumnos ordenados por Nombre</h2>
<%
    Dim Obj_Conn, Obj_RS, SQL

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS = Server.CreateObject("ADODB.RecordSet")

    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "SELECT * FROM Datos_Alumnos ORDER BY Nombre ASC"
    Obj_RS.Open SQL, Obj_Conn, 3, 3

    If Not Obj_RS.EOF Then
%>
        <table border="1">
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>DNI</th>
            </tr>
<%      Do While Not Obj_RS.EOF %>
            <tr>
                <td><%= Obj_RS("Nombre") %></td>
                <td><%= Obj_RS("Apellido") %></td>
                <td><%= Obj_RS("DNI") %></td>
            </tr>
<%          Obj_RS.MoveNext
        Loop %>
        </table>
<%  Else
        Response.Write "<p>No hay registros.</p>"
    End If

    Obj_RS.Close
    Obj_Conn.Close
    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>
</body>
</html>