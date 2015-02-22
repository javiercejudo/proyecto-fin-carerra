<?php
include ("sesion.php");
header ('Content-type: text/html; charset=iso-8859-1');

$q=$_GET["q"];
$ini=$_GET["ini"];
$numeroPorPagina=10;

$input=explode("-",$q);
$q=trim($input['0']);
$option=trim($input['1']);
$option2=trim($input['2']);

if(is_numeric($option2) && $option2!=0)
	$numeroPorPagina=ceil($option2);

$q = replace_accents($q);

function replace_accents($str) {
  $str = UTF8_encode($str);
  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
  $str = preg_replace('/&([a-zA-ZñÑ])(uml|acute|grave|circ);/','$1',$str);
  return html_entity_decode($str);
}

function b ($string) {
	$pos=stripos(replace_accents($string),$GLOBALS['q']);
	if($pos===false) return $string;
	$antes=substr($string,0,$pos);
	$marcado=substr($string,$pos,strlen($GLOBALS['q']));
	$despues=substr($string,$pos+strlen($GLOBALS['q']));
	$despues=b($despues);
	if (strlen($marcado)>2) return "<span style='background-color:transparent;'>".$antes."<b class='res_busqueda'>".$marcado."</b>".$despues."</span>";
	else return $antes.$marcado.$despues;
}

function b2 ($string) {
	$pos=stripos(replace_accents($string),$GLOBALS['q']);
	if($pos===false) return $string;
	$antes=substr($string,0,$pos);
	$marcado=substr($string,$pos,strlen($GLOBALS['q']));
	$despues=substr($string,$pos+strlen($GLOBALS['q']));
	$despues=b2($despues);
	if (strlen($marcado)>2) return $antes."<span class='res_busqueda''>".$marcado."</span>".$despues;
	else return $antes.$marcado.$despues;
}

$hint="";
$numberOfResults=0;
$numberShowed=0;
$numeroSumado=0;

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

if (strlen($q) > 0){
	$q2=$q;
	do{
		if($option!="p" && $option!="r"&& $option!="pt"&& $option!="rt"){
			$num_cli=$db->db_query("select count(*) as num_cli 
									from clientes cl 
									where cl.nombre like '".$q2."%' or cl.nombre like '% ".$q2."%' or
										cl.acronimo like '".$q2."%' or cl.acronimo like '% ".$q2."%' or
										cl.descripcion like '".$q2."%' or cl.descripcion like '% ".$q2."%' or
										cl.cif like '%".$q2."%' or 
										cl.contacto like '".$q2."%' or cl.contacto like '% ".$q2."%' or 
										cl.email like '".$q2."%' or 
										cl.telefono like '%".$q2."%' or 
										cl.direccion like '".$q2."%' or cl.direccion like '% ".$q2."%' or 
										cl.localidad like '".$q2."%' or cl.localidad like '% ".$q2."%' or 
										cl.provincia like '".$q2."%' or cl.provincia like '% ".$q2."%' or 
										cl.id='".$q."'"
									,true);
			$num_cli2=reset($num_cli);
			$num_cli=$num_cli2['num_cli'];
		} else $num_cli=0;

		if($option!="c" && $option!="p" && $option!="ct" && $option!="pt"){
			$num_per=$db->db_query("select count(*) as num_per 
									from personal pe join clientes cl on pe.id_cliente=cl.id 
									where pe.nombre like '$q2%' or pe.nombre like '% $q2%' or 
										  cl.nombre like '$q2%' or cl.nombre like '% $q2%' or 
										  cl.acronimo like '$q2%' or cl.acronimo like '% $q2%' 
									order by pe.nombre",true);
			$num_per2=reset($num_per);
			$num_per=$num_per2['num_per'];
		} else $num_per=0;

		if($option!="c" && $option!="r" && $option!="ct" && $option!="rt"){
			$num_pro=$db->db_query("select count(*) as num_pro 
									from proyectos pr join clientes cl on pr.cliente=cl.id 
									where pr.proyecto like '$q2%' or pr.proyecto like '% $q2%' or 
										pr.acronimo like '$q2%' or pr.acronimo like '% $q2%' or 
										pr.responsable like '$q2%' or pr.responsable like '% $q2%' or 
										pr.id='$q2' or 
										cl.nombre like '$q2%' or cl.nombre like '% $q2%' or 
										cl.acronimo like '$q2%' or cl.acronimo like '% $q2%'"
									,true);
			$num_pro2=reset($num_pro);
			$num_pro=$num_pro2['num_pro'];
		} else $num_pro=0;
		//echo $q2." / ";
		if($num_cli+$num_per+$num_pro==0) {
			$q2=substr($q2,0,strlen($q2)-1);
			$res0=1;
		}
	} while ($num_cli+$num_per+$num_pro==0 && strlen($q2)>2);
	
	if(isset($res0) && strlen($q2)>2) {
		echo "<span style='font-size:120%;color:Green;'>0 coincidencias: mostrando resultados más cercanos ('$q2...').</span>";
		$q=$q2;
	}
	
	if($option!="p" && $option!="r" && $option!="pt" && $option!="rt" ){
		$clientes=$db->db_query("select cl.* 
								from clientes cl left join proyectos pr on cl.id=pr.cliente 
								where cl.nombre like '".$q."%' or cl.nombre like '% ".$q."%' or
										cl.acronimo like '".$q."%' or cl.acronimo like '% ".$q."%' or
										cl.descripcion like '".$q."%' or cl.descripcion like '% ".$q."%' or
										cl.cif like '%".$q."%' or 
										cl.contacto like '".$q."%' or cl.contacto like '% ".$q."%' or 
										cl.email like '".$q."%' or 
										cl.telefono like '%".$q."%' or 
										cl.direccion like '".$q."%' or cl.direccion like '% ".$q."%' or 
										cl.localidad like '".$q."%' or cl.localidad like '% ".$q."%' or 
										cl.provincia like '".$q."%' or cl.provincia like '% ".$q."%' or 
										cl.id='".$q."' 
								group by cl.id 
								order by count(pr.id) desc,cl.nombre",true);
		$numdatos=count($clientes);
		if($numdatos > 0) {
			$numberOfResults+=$numdatos;
			$cli=reset($clientes);
			$hint.="<div class='searchType'>";
			//if ($numdatos>5 && $option!="c" && $option!="t") $numdatos=5
			for($i=0;$i<$numdatos;$i++){$numeroSumado++; if($numeroSumado>$ini && $numeroSumado<$ini+$numeroPorPagina+1 || $option=="t" || $option=="ct"){
				$hint.="<div class='rojo search'><a title='".$cli['nombre']."' href='info_cliente.php?id_cliente=".$cli['id']."'><u class=searchTitle>".b($cli['nombre']); //b(rtrim(substr($cli['nombre'],0,39)))
				//if (strlen($cli['nombre'])>40) $hint.=" <b>...</b>";
				$hint.=" (";
				if($cli['cif']!="") $hint.="".b($cli['cif'])."; ";
				$hint.="ID: ".b($cli['id']).")</u> </a><small style='padding:0 2px;border-radius:3px;color:white;background-color:#B14143;'>cliente</small></div>";
				if ($cli['descripcion']!=''){
					$hint.="<div class='searchDescription'>";
					if(strlen($cli['descripcion'])>300)
						$hint.=b2(substr($cli['descripcion'],0,299))."...";
					else
						$hint.=b2($cli['descripcion']);
					$hint.="</div>";
				} else
					$hint.="<div class='searchDescription'>No existe una descripción para este cliente</div>";
				$hint.="<div class=searchFoot>";
				if ($cli['acronimo']!='') $hint.=b($cli['acronimo'])."&nbsp;&nbsp;&nbsp;";
				if ($cli['contacto']!='') $hint.=b($cli['contacto'])."&nbsp;&nbsp;&nbsp;";
				if ($cli['email']!='') $hint.="<a class=rojo href='mailto:".$cli['email']."'>".b($cli['email'])."</a>&nbsp;&nbsp;&nbsp;";
				if ($cli['telefono']!='') $hint.=b($cli['telefono'])."&nbsp;&nbsp;&nbsp;";
				$direccion=explode(",",$cli['direccion']);
				$direccion=$direccion[0];
				if ($direccion!='') $hint.="<a href='http://maps.google.com/maps?q=".$direccion.", ".$cli['localidad'].", ".$cli['provincia']."'>".b($cli['direccion'])."&nbsp;&nbsp;&nbsp;";
				if ($cli['localidad']!='') $hint.=b($cli['localidad'])."&nbsp;&nbsp;&nbsp;";
				if ($cli['provincia']!='') $hint.=b($cli['provincia']);
				if ($direccion!='') $hint.="</a>";
				$hint.="</div>";
				$hint.="<table class='enlacesInteres' width='200px' cellspacing='0' cellpadding='0'>";
				$hint.="<tr><td width='50%'><a href='buscarproyecto.php?id_cliente=".$cli['id']."&activo=1&expe=1&activos=1&internos=1&mostrar_socios=1'>Proyectos</a></td><td width='50%'><a href='buscar_personal.php?id_cliente=".$cli['id']."'>Personal</a></td></tr>";
				$hint.="<tr><td width='50%'><a href='buscarfactura.php?id_cliente=".$cli['id']."'>Facturas</a></td><td width='50%'><a href='act_buscar.php?id_cliente=".$cli['id']."'>Actividades</a></td></tr>";
				$hint.="<tr><td width='50%'><a href='buscarcontrato.php?id_cliente=".$cli['id']."'>Contratos</a></td><td width='50%'><a href='archivos_cliente.php?id_cliente=".$cli['id']."'>Archivos</a></td></tr>";
				$hint.="</table>";
				$hint.="<br>";
				$numberShowed++;
				}
				$cli=next($clientes);
			}
			$hint.="</div>";
		}
	}
	if($option!="c" && $option!="p" && $option!="ct" && $option!="pt" && $numberShowed<$numeroPorPagina || $option=="t"){
		$personal=$db->db_query("select cl.nombre as nom_cli,cl.acronimo as acr_cli,pe.* 
								  from personal pe join clientes cl on pe.id_cliente=cl.id
								  where pe.nombre like '$q%' or pe.nombre like '% $q%' or 
										cl.nombre like '$q%' or cl.nombre like '% $q%' or 
										cl.acronimo like '$q%' or cl.acronimo like '% $q%' 
								  order by pe.nombre",true);

		$numdatos=count($personal);
		if($numdatos > 0) {
			$numberOfResults+=$numdatos;
			$per=reset($personal);
			$hint.="<div class='searchType'>";
			//if ($numdatos>5 && $option!="r" && $option!="t") $numdatos=5;
			for($i=0;$i<$numdatos;$i++){$numeroSumado++;if($numeroSumado>$ini && $numeroSumado<$ini+$numeroPorPagina+1 || $option=="t" || $option=="rt"){
				$hint.="<div class='rojo search'><a href='buscar_personal.php?id=".$per['id']."'><u class=searchTitle>".b(ucwords(strtolower($per['nombre'])));
				$hint.=" (ID: ".$per['id'].")</u></a> <small style='padding:0 2px;border-radius:3px;color:white;background-color:#349545;'>recurso</small></div>";
				$hint.="<div class='searchDescription'>";
				if($per['curriculum']!='') $hint.=$per['curriculum']."<br>";
				$hint.="<span class='rojo'>Cliente:</span> <i>".b($per['nom_cli']);
				if($per['nom_cli']!=$per['acr_cli']) $hint.=" - ".b($per['acr_cli']);
				$hint.="</i></div>";
				$hint.="<div class=searchFoot>";
				if ($per['titulacion']!='') $hint.=$per['titulacion']."&nbsp;&nbsp;&nbsp;";
				if ($per['ubicacion']!='') $hint.=$per['ubicacion']."&nbsp;&nbsp;&nbsp;";
				if ($per['fecha_alta']!='0') $hint.=date('d-m-Y',$per['fecha_alta'])."&nbsp;&nbsp;&nbsp;";
				if ($per['fecha_baja']!='0') $hint.=date('d-m-Y',$per['fecha_baja'])."&nbsp;&nbsp;&nbsp;";
				if ($per['grupo_cotizacion']!='') $hint.="<abbr title='Grupo de cotización'>GC</abbr>: ".b($per['grupo_cotizacion'])."&nbsp;&nbsp;&nbsp;";
				$hint.="</div>";
				/*$hint.="<table class='enlacesInteres' width='200px' cellspacing='0' cellpadding='0'>";
				$hint.="<tr><td width='75%'><a href='buscar_personal.php?id=".$per['id']."&id_cliente=".$per['id_cliente']."'>Datos anuales</a></td><td width='25%'><!--<a href='buscar_personal.php?id_cliente=".$pro['id']."'>Personal</a>--></td></tr>";
				$hint.="<tr><td width='50%'><!--<a href='listado.php?proyecto=".$pro['id']."&list=9'>Facturas</a>--></td><td width='50%'><!--<a href='act_buscar.php?id_proyecto=".$pro['id']."'>Actividades</a>--></td></tr>";
				$hint.="</table>";*/
				$hint.="<br>";
				$numberShowed++;
				}
				$per=next($personal);
			}
			$hint.="</div>";
		}
	}
	if($option!="c" && $option!="r" && $option!="ct" && $option!="rt" && $numberShowed<$numeroPorPagina || $option=="t"){
		$proyectos=$db->db_query("select count(ex.id) as num_exp, cl.nombre as nom_cli, cl.acronimo as acr_cli, pr.* 
								  from expediente ex right join proyectos pr on ex.proyecto=pr.id join clientes cl on pr.cliente=cl.id 
								  where pr.proyecto like '$q%' or pr.proyecto like '% $q%' or 
										pr.acronimo like '$q%' or pr.acronimo like '% $q%' or 
										pr.responsable like '$q%' or pr.responsable like '% $q%' or 
										pr.id='$q' or 
										cl.nombre like '$q%' or cl.nombre like '% $q%' or 
										cl.acronimo like '$q%' or cl.acronimo like '% $q%' 
								  group by pr.id 
								  order by num_exp desc,pr.id desc",true);

		$numdatos=count($proyectos);
		if($numdatos > 0) {
			$numberOfResults+=$numdatos;
			$pro=reset($proyectos);
			$hint.="<div class='searchType'>";
			//if ($numdatos>5 && $option!="p" && $option!="t") $numdatos=5;
			for($i=0;$i<$numdatos;$i++){$numeroSumado++;if($numeroSumado>$ini && $numeroSumado<$ini+$numeroPorPagina+1 || $option=="t" || $option=="pt"){
				$hint.="<div class='rojo search'><a href='javascript:;' onclick='window.open(\"verproyecto.php?id=".$pro['id']."\",\"\",\"width=800,height=600,scrollbars=yes\")'><u class=searchTitle>";
				if($pro['acronimo']!='') $hint.= b($pro['acronimo']);
				else {
					$hint.= b(trim(substr($pro['proyecto'],0,49)));
					if (strlen($pro['proyecto'])>50) $hint.= " <b>...</b>";
				}
				//b(rtrim(substr($pro['proyecto'],0,49)));
				//if (strlen($pro['proyecto'])>50) $hint.=" <b>...</b> ";
				$hint.=" (ID: ".b($pro['id']).")</u></a> <small style='padding:0 2px;border-radius:3px;color:white;background-color:#4241B1;'>proyecto</small></div>";
				$hint.="<div class='searchDescription'>".b2($pro['proyecto'])." <br><span class='rojo'>Cliente:</span> <em>".b($pro['nom_cli']);
				if($pro['nom_cli']!=$pro['acr_cli']) $hint.=" - ".b($pro['acr_cli']);
				$hint.="</em></div>";
				$hint.="<div class=searchFoot>";
				//if ($pro['acronimo']!='') $hint.=b($pro['acronimo'])."&nbsp;&nbsp;&nbsp;";
				if ($pro['tipo']!='') $hint.=b($pro['tipo'])."&nbsp;&nbsp;&nbsp;";
				if ($pro['estado']!='') $hint.=b($pro['estado'])."&nbsp;&nbsp;&nbsp;";
				if ($pro['responsable']!='0') $hint.=b($pro['responsable'])."&nbsp;&nbsp;&nbsp;";
				if ($pro['fecha']!='0') $hint.=date('d-m-Y',$pro['fecha'])."&nbsp;&nbsp;&nbsp;";
				if ($pro['presupuesto']!='0') $hint.="<abbr title='Presupuesto'>Pres.</abbr>: ".number_format($pro['presupuesto'],2,',','.')."&nbsp;€&nbsp;&nbsp;&nbsp;";
				if ($pro['honorarios']!='0') $hint.="<abbr title='Honorarios'>Hon.</abbr>: ".number_format($pro['honorarios'],2,',','.')."&nbsp;€";
				$hint.="</div>";
				$hint.="<table class='enlacesInteres' width='200px' cellspacing='0' cellpadding='0'>";
				$hint.="<tr><td width='50%'><a href='buscarproyecto.php?id_proyecto=".$pro['id']."&expe=1&mostrar_socios=1'>Expedientes</a></td><td width='50%'><!--<a href='buscar_personal.php?id_cliente=".$pro['id']."'>Personal</a>--></td></tr>";
				$hint.="<tr><td width='50%'><a href='listado.php?proyecto=".$pro['id']."&list=9'>Facturas</a></td><td width='50%'><a href='act_buscar.php?id_proyecto=".$pro['id']."'>Actividades</a></td></tr>";
				$hint.="</table>";
				$hint.="<br>";
				$numberShowed++;
				}
				$pro=next($proyectos);
			}
			$hint.="</div>";
		}
	}
}
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

$numberOfResults=$num_cli+$num_per+$num_pro;

echo "<input id='numberOfResults' value=".$numberOfResults." type='hidden'>";
echo "<input id='numeroPorPagina' value=".$numeroPorPagina." type='hidden'>";
if ($numberOfResults == 0){
	echo "<br><div style='font-size:120%;'>No se encontró ningún resultado.";
	echo "<ul>";
	echo "<li>Comprueba que todas las palabras están escritas correctamente.</li>";
	echo "<li>Intenta usar otras palabras.</li>";
	echo "</ul></div>";
}
else{
	echo "<small><div style='height:5px;'></div>";
	if ($numberOfResults>1) {
		if($option!="t" && $option!="ct" && $option!="rt" && $option!="pt")
			echo "Página ".number_format($ini/$numeroPorPagina+1)." de ".number_format(ceil($numberOfResults/$numeroPorPagina));
		else echo " Todos los resultados";
		//echo " al ".number_format($ini/$numeroPorPagina+$numberShowed);
		echo " (".number_format($totaltime,2)." segundos, ".$numberOfResults." resultados). Actualizado a las ".date('h:i:s',time()).".";

		if($numberOfResults>$numeroPorPagina && $option!="t" && $option!="ct" && $option!="rt" && $option!="pt") {
			echo "<span class='searchNav2'>";
			$limA=$ini/$numeroPorPagina-4;
			if ($limA<1) $limA=1;

			//if($ini!=0) echo "<a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),0)'>««</a>";	
			if($ini!=0) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini-$numeroPorPagina).");'>«</a> ";
			if($limA>1){
				echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),0);'>1</a>";
				if($limA!=2)
					echo " ... ";
			}

			for ($i=$limA;$i<$ini/$numeroPorPagina+1;$i++){
				if($ini/$numeroPorPagina!=$i-1) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(($i-1)*$numeroPorPagina).");'>";
				else echo " <b class=rojo>";
				echo number_format($i);
				if($ini/$numeroPorPagina!=$i-1) echo "</a> ";
				else echo "</b> ";
			}
		
			$ultima_pag=1;
			for ($i=$ini/$numeroPorPagina+1;$i<=ceil($numberOfResults/$numeroPorPagina) && $i<$ini/$numeroPorPagina+7;$i++){
				if($ini/$numeroPorPagina!=$i-1) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(($i-1)*$numeroPorPagina).");'>";
				else echo " <b class=rojo>";
				echo number_format($i);
				if($ini/$numeroPorPagina!=$i-1) echo "</a> ";
				else echo "</b> ";
				$ultima_pag=$i;
			}
			if($ultima_pag<number_format(ceil($numberOfResults/$numeroPorPagina))){
				if($ultima_pag<ceil($numberOfResults/$numeroPorPagina)-1)
					echo " ... ";
				echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(floor(($numberOfResults-1)/$numeroPorPagina)*$numeroPorPagina).");'>".number_format(ceil($numberOfResults/$numeroPorPagina))."</a> ";
			}
			if($ini+$numberShowed<$numberOfResults) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini+$numeroPorPagina).");'>»</a>";
			//if($ini+$numberShowed<$numberOfResults) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(floor(($numberOfResults-1)/$numeroPorPagina)*$numeroPorPagina).");'>»»</a>";
			echo "</span>";
		}
	}
	else
		echo "Se encontró 1 resultado en ".number_format($totaltime,2)." segundos.";
	echo "</small>";

	/*if($ini!=0 || $ini+$numberShowed<$numberOfResults) echo "<div style='height:10px;width:500px;'></div><div class='searchNav'>";
	if($ini!=0) echo "<a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini-5).");'>« anterior</a>";
	if($ini+$numberShowed<$numberOfResults) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini+5).");'>siguiente »</a>";
	if($ini!=0 || $ini+$numberShowed<$numberOfResults) echo "</div><div style='height:10px;width:500px;'></div>";*/
	
	echo "<div style='height:20px;'></div>".$hint."<br>";

	if($numberOfResults>$numeroPorPagina && $option!="t" && $option!="ct" && $option!="rt" && $option!="pt") {
		if($ini!=0 || $ini+$numberShowed<$numberOfResults) echo "<div class='searchNav'>";
		$limA=$ini/$numeroPorPagina-4;
		if ($limA<1) $limA=1;

		//if($ini!=0) echo "<a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),0)'>««</a>";	
		if($ini!=0) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini-$numeroPorPagina).");'>« anterior</a> ";
		if($limA>1){
			echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),0);'>1</a>";
			if($limA!=2)
				echo " ... ";
		}

		for ($i=$limA;$i<$ini/$numeroPorPagina+1;$i++){
			if($ini/$numeroPorPagina!=$i-1) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(($i-1)*$numeroPorPagina).");'>";
			else echo " <b class=rojo>";
			echo number_format($i);
			if($ini/$numeroPorPagina!=$i-1) echo "</a> ";
			else echo "</b> ";
		}
	
		$ultima_pag=1;
		for ($i=$ini/$numeroPorPagina+1;$i<=ceil($numberOfResults/$numeroPorPagina) && $i<$ini/$numeroPorPagina+7;$i++){
			if($ini/$numeroPorPagina!=$i-1) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(($i-1)*$numeroPorPagina).");'>";
			else echo " <b class=rojo>";
			echo number_format($i);
			if($ini/$numeroPorPagina!=$i-1) echo "</a> ";
			else echo "</b> ";
			$ultima_pag=$i;
		}
		if($ultima_pag!=number_format(ceil($numberOfResults/$numeroPorPagina))){
			if($ultima_pag<number_format(ceil($numberOfResults/$numeroPorPagina))-1)
				echo " ... ";
			echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(floor(($numberOfResults-1)/$numeroPorPagina)*$numeroPorPagina).");'>".number_format(ceil($numberOfResults/$numeroPorPagina))."</a> ";
		}
		if($ini+$numberShowed<$numberOfResults) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format($ini+$numeroPorPagina).");'>» siguiente</a>";
		//if($ini+$numberShowed<$numberOfResults) echo " <a href='javascript:;' onclick='searchBoxUp(document.getElementById(\"searchBox\"),".number_format(floor(($numberOfResults-1)/$numeroPorPagina)*$numeroPorPagina).");'>»»</a>";
		echo "</div>";
	}
	
	echo "<br><br>";
}
?>
