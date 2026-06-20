<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Alumno</title>
</head>
<body>

<%
If Request.Form("Nombre") = "" Then
%>

    <FORM METHOD="POST" ACTION="ejem_8.asp">
        <H2>Inserte sus datos</H2>

        Nombre:
        <INPUT TYPE="TEXT" NAME="Nombre"><BR><BR>

        Apellidos:
        <INPUT TYPE="TEXT" NAME="Apellidos"><BR><BR>

        DNI:
        <INPUT TYPE="TEXT" NAME="DNI"><BR><BR>

        Dirección:
        <INPUT TYPE="TEXT" NAME="Direccion"><BR><BR>

        Teléfono:
        <INPUT TYPE="TEXT" NAME="Telefono"><BR><BR>

        <INPUT TYPE="SUBMIT" VALUE="Enviar">
    </FORM>

<%
Else

    Dim Obj_Conn, Obj_RS
    Dim Nombre, Apellidos, DNI, Direccion, Telefono

    Nombre = Request.Form("Nombre")
    Apellidos = Request.Form("Apellidos")
    DNI = Request.Form("DNI")
    Direccion = Request.Form("Direccion")
    Telefono = Request.Form("Telefono")

    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS = Server.CreateObject("ADODB.Recordset")

    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=C:\inetpub\wwwroot\App_web\Practica_6\Base_Datos\Alumnos.accdb;Persist Security Info=False;"

    Obj_RS.Open "SELECT * FROM Datos_Alumnos", Obj_Conn, 2, 3

    Obj_RS.AddNew

    Obj_RS("Nombre") = Nombre
    Obj_RS("Apellidos") = Apellidos
    Obj_RS("DNI") = DNI
    Obj_RS("Direccion") = Direccion
    Obj_RS("Telefono") = Telefono

    Obj_RS.Update

    Obj_RS.Close
    Obj_Conn.Close

    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>

    <CENTER>
        <H1>DATOS INSERTADOS CORRECTAMENTE</H1>

        <P><B>Nombre:</B> <%=Nombre%></P>
        <P><B>Apellidos:</B> <%=Apellidos%></P>
        <P><B>DNI:</B> <%=DNI%></P>
        <P><B>Direccion:</B> <%=Direccion%></P>
        <P><B>Telefono:</B> <%=Telefono%></P>
    </CENTER>

<%
End If
%>

</body>
</html>