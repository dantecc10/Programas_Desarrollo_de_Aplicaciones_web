<%@ Language="VBScript" %>
<% Option Explicit %>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de una tabla a través de SQL</title>
</head>
<body>
<%
    ' Declaración de variables
    Dim Obj_Conn, Obj_RS, SQL

    ' Configuración de objetos
    Set Obj_Conn = Server.CreateObject("ADODB.Connection")
    Set Obj_RS = Server.CreateObject("ADODB.RecordSet")

    ' Cadena SQL y conexión
    SQL = "SELECT * FROM Datos_Alumnos"
    ' NOTA: Asegúrate de que "Alumnos" sea un DSN configurado en el servidor, 
    ' si usas el archivo directo, usa la ruta completa con el proveedor ACE.OLEDB.12.0
    ' Prueba con el proveedor 16.0 (si tienes instalado Access 2016 o posterior)

    Obj_Conn.Open "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" & Server.MapPath("Base_Datos/Alumnos.accdb")


    Obj_RS.Open SQL, Obj_Conn, 3, 3

    ' Verificación de registros
    If Obj_RS.EOF Then
        Response.Write "<CENTER><H1>NO EXISTEN REGISTROS</H1></CENTER>"
    Else
%>
        <TABLE BORDER="1" ALIGN="CENTER">
            <TR>
                <TH>Nombre</TH>
                <TH>Apellidos</TH>
                <TH>D.N.I</TH>
            </TR>
            <% Do While Not Obj_RS.EOF %>
                <TR>
                    <TD><%= Obj_RS("Nombre") %></TD>
                    <TD><%= Obj_RS("Apellido") %></TD>
                    <TD><%= Obj_RS("DNI") %></TD>
                </TR>
            <% 
                Obj_RS.MoveNext
            Loop 
            %>
        </TABLE>
<%
    End If

    ' Cierre y limpieza
    Obj_RS.Close
    Obj_Conn.Close
    Set Obj_RS = Nothing
    Set Obj_Conn = Nothing
%>
</body>
</html>