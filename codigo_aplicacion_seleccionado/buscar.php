<?php
	include ("include/sesion.php");
	
	//Eliminamos Cache del Navegador
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");

	$q=$_REQUEST['q'];

	$input=explode("-",$q);
	$q=trim($input['0']);
	$option=trim($input['1']);
	$option2=trim($input['2']);
	if($option) $q.=" -".$option;
	if($option2) $q.=" -".$option2;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<title>Buscador | Ingeniería e Innovación | Consultora especializada en la gestión de la I+D+i</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="keywords" content="ingeniería,innovación,Ingeniería e Innovación,consultora,I+D+i,innova">
	<meta name="description" content="Ingenier&iacute;a e Innovaci&oacute;n es una consultora especializada en la gesti&oacute;n de la I+D+i cuya actividad principal es el asesoramiento especializado a las empresas en la gesti&oacute;n de la I+D+i.">
	<link rel="stylesheet" type="text/css" href="include/estilos.css">
	<link rel="shortcut icon" href="favicon.ico">
	<script src="include/buscador.js" type="text/javascript"></script>
</head>
<body onLoad="searchBoxFocusB(document.getElementById('searchBox'));" onkeyup="teclas(event)" style="overflow-y:scroll;">
<?php
include ("header.php"); 
echo "<div id='cuerpo'>";
if ($nivel>2){
	echo "<div id='menu'>";
	include ('menu.php');
	echo "</div>";
	echo "<div id='contenido'>";
	
	echo "<div style='width:720px;'>";
	//echo "<div style='width:600px;text-align:center;position:relative;'><img src='iandi.png'></div><br>";
	echo "<form action='buscar.php' method='get'>";
	echo "<input type='text' style='width:620px;margin-right:10px;padding:5px;font-size:160%;color:#666666;' id='searchBox' tabindex='1' onFocus='searchBoxFocus(this)' onBlur='searchBoxBlur(this)' onkeyup='searchBoxFocus(this);' onChange='searchBoxChange(this)' autocomplete='off' name='q' hasFocus>";
	echo "<img src='imagenes/loading2.png' alt='' class='img_abajo' id='buscando'>";
	echo "</form>";
	echo "</div>";
	echo "<div id='searchResults' style='display:block;width:632px;'>";
	
	echo "<p ><strong>Ejemplos de búsqueda (se puede pinchar sobre ellos):</strong></p>";
	echo "<table border=0 cellpadding=7 cellspacing=7>";
	echo "<tr><td align='right' valign='top' width='135px'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='Ingeniería e Innovación';document.getElementById('searchBox').focus();\">Ingeniería e Innovación</a>, <a href='javascript:;' onclick=\"document.getElementById('searchBox').value='bacteria acética';document.getElementById('searchBox').focus();\">bacteria acética</a>, <a href='javascript:;' onclick=\"document.getElementById('searchBox').value='Pedro';document.getElementById('searchBox').focus();\">Pedro</a></i></td><td>Búsqueda simple. Nos da <b>información básica</b> del elemento buscado y acceso a <b>proyectos, facturas, contratos, personal</b>...</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='941238675';document.getElementById('searchBox').focus();\">941238675</a></i></td><td>Búsqueda de cualquier campo. ¿De quién es este número?</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='solar -c';document.getElementById('searchBox').focus();\">solar -c</a></i></td><td>Búsqueda con parámetro. -c sólo muestra <b>clientes</b>. Puede usarse la ayuda &rarr;</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='sca -p';document.getElementById('searchBox').focus();\">sca -p</a></i></td><td>Búsqueda con parámetro. -p sólo muestra <b>proyectos</b>.</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='iandi -r';document.getElementById('searchBox').focus();\">iandi -r</a></i></td><td>Búsqueda con parámetro. -r sólo muestra <b>recursos</b>.</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='desarrollo --3';document.getElementById('searchBox').focus();\">desarrollo --3</a></i></td><td>Mostrar sólo <b>3</b> <b>resultados (cualquier tipo)</b> por página.</td></tr>";
	echo "<tr><td align='right' valign='top'><i><a href='javascript:;' onclick=\"document.getElementById('searchBox').value='desarrollo -p-2';document.getElementById('searchBox').focus();\">desarrollo -p-2</a></i></td><td>Mostrar sólo <b>2</b> <b>proyectos</b> por página.</td></tr>";
	echo "</table>";
	

	echo "</div>";
	
	echo "</div>";
	echo "<div id='avanzado' style='border-left:1px solid grey;padding-left:10px;'><span class=rojo style='font-size:14px;'>Ayuda:</span><br><br>";
	
	//echo "<a href=\"javascript:;\" onclick=\"document.getElementById('searchBox').value=document.getElementById('searchBox').value.split(' -')[0].trim()+' -t';document.getElementById('searchBox').focus();\">Todo los resultados en la misma página: <b>-t</b></a><br><br>";
	
	echo "<a href=\"javascript:;\" onclick=\"document.getElementById('searchBox').value=document.getElementById('searchBox').value.trim().split('-')[0].trim()+' -c';document.getElementById('searchBox').focus();\">Sólo clientes: <b>-c</b></a><br>";
	echo "<a href=\"javascript:;\" onclick=\"document.getElementById('searchBox').value=document.getElementById('searchBox').value.trim().split('-')[0].trim()+' -r';document.getElementById('searchBox').focus();\">Sólo recursos: <b>-r</b></a><br>";
	echo "<a href=\"javascript:;\" onclick=\"document.getElementById('searchBox').value=document.getElementById('searchBox').value.trim().split('-')[0].trim()+' -p';document.getElementById('searchBox').focus();\">Sólo proyectos: <b>-p</b></a>";

	echo "<br><br><a href='buscar.php'>Ver ejemplos</a>";
	//echo "El caracter <i>%</i> sustituye cualquier cadena de caracteres.";
	
	echo "</div>";
} else {
	echo "<script type='text/javascript'>";
	echo "location.href='index.php?ref='+encodeURIComponent(location.href);";
	echo "</script>";
}
echo "</div>";
?>
</body>
</html>
