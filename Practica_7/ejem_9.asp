<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contar registros (COUNT)</title>
</head>
<body>
<%
    Dim Obj_Conn, Obj_RS, SQL, total

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS = Server.CreateObject("ADODB.RecordSet")

    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")

    SQL = "SELECT COUNT(*) AS Total FROM Datos_Alumnos"
    Obj_RS.Open SQL, Obj_Conn, 3, 3

    total = Obj_RS("Total")

    Response.Write "<h2>Total de alumnos registrados: " & total & "</h2>"

    Obj_RS.Close
    Obj_Conn.Close
    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>
<br><a href='ejem_1.asp'>Ver listado completo</a>
</body>
</html>