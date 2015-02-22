<?php
	include ("include/sesion.php");
	
		//Eliminamos Cache del Navegador
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");

	$nombre=array("1"=>"Enero", "2"=>"Febrero", "3"=>"Marzo", "4"=>"Abril", "5"=>"Mayo", "6"=>"Junio", "7"=>"Julio", "8"=>"Agosto", "9"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<title>Buscar Actividades | Ingeniería e Innovación | Consultora especializada en la gestión de la I+D+i</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="keywords" content="ingeniería,innovación,Ingeniería e Innovación,consultora,I+D+i,innova">
	<meta name="description" content="Ingenier&iacute;a e Innovaci&oacute;n es una consultora especializada en la gesti&oacute;n de la I+D+i cuya actividad principal es el asesoramiento especializado a las empresas en la gesti&oacute;n de la I+D+i.">
	<link rel="stylesheet" type="text/css" href="include/calendario.css">
	<link rel="stylesheet" type="text/css" href="include/estilos.css">
	<link rel="shortcut icon" href="favicon.ico">
	<script src="include/script.js" type="text/javascript"></script>
	<script src="include/sorttable.js" type="text/javascript"></script>
	<script type="text/javascript">
		function cambiar_horas (pe,ac,an,me,h,tipo) {
			h = prompt("Cambiar número de horas:",h);
			if (h!=null) {
				location.href="act_cambio_horas_mes.php?pe="+pe+"&ac="+ac+"&an="+an+"&me="+me+"&h="+h+"&tipo="+tipo+"&ref="+encodeURIComponent(location.href);
				setTimeout(window.opener.location.reload(true),100);
			}
		}
	</script>
</head>
<body>
<div style="margin:15px;">
<?
  if ($nivel>2){
?>
	<div>
		<?php
			$id_cliente=$_REQUEST['id_cliente'];
			$id_personal=$_REQUEST['id_personal'];
			$anio=$_REQUEST['anio'];
			$mes=$_REQUEST['mes'];
			$id_proyecto=$_REQUEST['id_proyecto'];
			$id_actividad=$_REQUEST['id_actividad'];

			$query="select p.nombre as nom_per,cp.coste_h, cl.acronimo as acr_cli, pr.acronimo as acr_pro, pr.id as id_pro, pr.estado as est_pro, ac.nombre as nom_act,cl.id as id_cli, ph.* from coste_personal cp join personal p on cp.id_personal=p.id join personal_actividad pa on p.id=pa.id_personal join personal_horas ph on (pa.id_personal=ph.id_personal and pa.id_actividad=ph.id_actividad) join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id join clientes cl on pr.cliente=cl.id where cp.anio=ph.anio";
			$query_aux1="select cl.id, cl.acronimo as acr_cli from coste_personal cp join personal p on cp.id_personal=p.id join personal_actividad pa on p.id=pa.id_personal join personal_horas ph on (pa.id_personal=ph.id_personal and pa.id_actividad=ph.id_actividad) join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id join clientes cl on pr.cliente=cl.id where cp.anio=ph.anio";
			
			$query2="";
			
			if ($id_cliente!='') {
				$query.=" and cl.id=".$id_cliente;
				$query_aux1.=" and cl.id=".$id_cliente;
			} 
			
			if ($id_personal!='') {
				$query2.=" and ph.id_personal=".$id_personal;
			}
			
			if ($anio!='') {
				$query2.=" and ph.anio=".$anio;
			}

			if ($mes!='') {
				$query2.=" and ph.mes=".$mes;
			}
			
			if ($id_proyecto!='') {
				$query2.=" and pr.id=".$id_proyecto;
			}

			if ($id_actividad!='') {
				$query2.=" and ph.id_actividad=".$id_actividad;
			}
			
			$query_aux2=$query2;

			$query3=" group by ph.id_personal, ph.anio,ph.mes,ph.id_actividad order by ph.anio, ph.mes,nom_per,acr_pro,nom_act";
			$query_aux3= " group by cl.id";
			

			$p_h_aux=$db->db_query($query_aux1.$query_aux2.$query_aux3,true);
			$num_clientes=count($p_h_aux);
			
			$personal_horas=$db->db_query($query.$query2.$query3,true);
			$num_datos=count($personal_horas);
			$advertir=0;
			if ($num_datos!=0) {
				$p_h=reset($personal_horas);
				if($id_personal) $id_cliente=$p_h['id_cli'];//correccion para colaboraciones
				if($id_proyecto) $id_cliente=$p_h['id_cli'];
				if($id_actividad) {$id_proyecto=$p_h['id_pro'];$id_cliente=$p_h['id_cli'];}
			} else {
				$advertir=1;
				$personal_horas=$db->db_query($query.$query3,true);
				$p_h=reset($personal_horas);
				$num_datos=0;//count($personal_horas)
			}
			//else { echo "Error 1: No se encontraron datos."; exit();}

			if(!$id_cliente) {echo "Error 2: No se encontraron datos. Compruebe que no dejó en blanco a la vez los campos de cliente, recurso, proyecto y actividad.";exit();}

			echo "<div id='cabecera_tabla' style='min-width:750px;position:fixed;top:0px;background-color:white;padding:10px 0;width:98%;border-bottom:1px solid black;z-index:2;'>";

			?>
			
			<form name="plan" action="plan_pasarela.php" method="get">
			<?php //echo $query_aux1.$query_aux2.$query_aux3;?>
				<h2 class='rojo esp_abajo'>
			<?php
				echo "<select name='id_cliente' onchange='//submit()'>";
				echo "<option value=''>Cliente (todos)</option>";
				for ($i = 0; $i < $num_clientes; $i++) {
					//if($i==0) echo "<a href='plan.php?id_cliente=".$p_h_aux[$i]['id']."'>".$p_h_aux[$i]['acr_cli']."</a> ";
					//else echo ", <a href='plan.php?id_cliente=".$p_h_aux[$i]['id']."'>".$p_h_aux[$i]['acr_cli']."</a> ";
					echo "<option value='".$p_h_aux[$i]['id']."'";
					if ($id_cliente==$p_h_aux[$i]['id'] && $num_clientes<2)echo " selected";
					echo ">".getAcrCli($p_h_aux[$i]['id'])."</option>";
				}
				echo "</select>";
			?>
				<!--<input name="id_cliente" type="hidden" value="<?php echo $id_cliente?>">-->
				»
				<select name="id_personal" onchange="//submit()">
					<option value="">Recurso (todos)</option>
					<?php
				
					$recursos=$db->db_query("select pe.id,pe.nombre from personal pe where id_cliente=$id_cliente order by pe.nombre",true);
					$num_rec=count($recursos);
					$rec=reset($recursos);
					for($cont=0;$cont<$num_rec;$cont++) {
						echo "<option value='".$rec['id']."'";
						if($rec['id']==$id_personal) echo "selected";
						echo ">".$rec['nombre']."</option>";
						$rec=next($recursos);
					}

					$query_personal_asociado="select pe.id, pe.nombre from personal pe join cooperacion co on pe.id_cliente=co.cliente join proyectos pr on co.proyecto=pr.id where pr.cliente=".$id_cliente." group by pe.id order by pe.nombre";
					$pers_asoc=$db->db_query($query_personal_asociado,true);
					if(count($pers_asoc)!=0) $p_a=reset($pers_asoc);

					$num_rec_asoc=count($pers_asoc);
					for($cont=0;$cont<$num_rec_asoc;$cont++) {
						echo "<option value='".$p_a['id']."'";
						if($p_a['id']==$id_personal) echo "selected";
						echo ">".$p_a['nombre']." (coop.) </option>";
						$p_a=next($pers_asoc);
					}
					
					?>
				</select>
				<?//echo $id_cliente." ".$query_personal_asociado?>
				»
				<!--<input value="20XX" maxlength="4" style="border:0;font-size:110%;font-weight:bold;width:45px;" class=rojo>-->
				<select name="anio" onchange="//submit()">
					<option value="">Año (todos)</option>
					<option <?php if($anio==2005) echo "selected"?>>2005</option>
					<option <?php if($anio==2006) echo "selected"?>>2006</option>
					<option <?php if($anio==2007) echo "selected"?>>2007</option>
					<option <?php if($anio==2008) echo "selected"?>>2008</option>
					<option <?php if($anio==2009) echo "selected"?>>2009</option>
					<option <?php if($anio==2010) echo "selected"?>>2010</option>
					<option <?php if($anio==2011) echo "selected"?>>2011</option>
					<option <?php if($anio==2012) echo "selected"?>>2012</option>
					<option <?php if($anio==2013) echo "selected"?>>2013</option>
					<option <?php if($anio==2014) echo "selected"?>>2014</option>
					<option <?php if($anio==2015) echo "selected"?>>2015</option>
					<option <?php if($anio==2016) echo "selected"?>>2016</option>
					<option <?php if($anio==2017) echo "selected"?>>2017</option>
					<option <?php if($anio==2018) echo "selected"?>>2018</option>
					<option <?php if($anio==2019) echo "selected"?>>2019</option>
					<option <?php if($anio==2020) echo "selected"?>>2020</option>
				</select>
				»
				<select name="mes" onchange="//submit()">
					<option value="">Mes (todos)</option>
					<option value="1" <?php if($mes==1) echo "selected"?>>Enero</option>
					<option value="2" <?php if($mes==2) echo "selected"?>>Febrero</option>
					<option value="3" <?php if($mes==3) echo "selected"?>>Marzo</option>
					<option value="4" <?php if($mes==4) echo "selected"?>>Abril</option>
					<option value="5" <?php if($mes==5) echo "selected"?>>Mayo</option>
					<option value="6" <?php if($mes==6) echo "selected"?>>Junio</option>
					<option value="7" <?php if($mes==7) echo "selected"?>>Julio</option>
					<option value="8" <?php if($mes==8) echo "selected"?>>Agosto</option>
					<option value="9" <?php if($mes==9) echo "selected"?>>Septiembre</option>
					<option value="10" <?php if($mes==10) echo "selected"?>>Octubre</option>
					<option value="11" <?php if($mes==11) echo "selected"?>>Noviembre</option>
					<option value="12" <?php if($mes==12) echo "selected"?>>Diciembre</option>
				</select>
				»
				<select name="id_proyecto" onchange="document.getElementById('id_actividad').value='';//submit()">
					<option value="">Proyecto (todos)</option>
					<?php
					$proyectos=$db->db_query("select pr.id,pr.acronimo from proyectos pr where cliente=$id_cliente order by pr.acronimo",true);
					$num_pro=count($proyectos);
					$pro=reset($proyectos);
					for($cont=0;$cont<$num_pro;$cont++) {
						echo "<option value='".$pro['id']."'";
						if($pro['id']==$id_proyecto) echo "selected";
						echo ">".$pro['acronimo']."</option>";
						$pro=next($proyectos);
					}
					?>
				</select>
				<!--select cl.nombre,co.proyecto from clientes cl join cooperacion co on cl.id=co.cliente where cl.id=141-->
				<?php
					echo "» ";
					echo "<select id='id_actividad' name='id_actividad' onchange='//submit()'>";
					echo "	<option value=''>Actividad (todas)</option>";
				if($id_proyecto){
						$actividades=$db->db_query("select ac.id,ac.nombre from actividad ac where proyecto=$id_proyecto order by ac.nombre",true);
						$num_act=count($actividades);
						$act=reset($actividades);
						for($cont=0;$cont<$num_act;$cont++) {
							echo "<option value='".$act['id']."'";
							if($act['id']==$id_actividad) echo "selected";
							echo ">".$act['nombre']."</option>";
							$act=next($actividades);
						}
				}
					echo "</select>";
				?>
				&nbsp;&nbsp;<input type="submit" value="Filtrar">
				</h2>
			</form>
			
			<?php
			
			//echo $query."<br>";

			/*echo "<h2 class='rojo esp_abajo'>";
			if ($id_cliente) echo "<a href='plan.php?id_cliente=".$id_cliente."'>".$p_h['acr_cli']."</a>";
			if ($id_personal) echo " <span class=negro>»</span> <a href='plan.php?id_cliente=".$id_cliente."&id_personal=".$id_personal."'>".$p_h['nom_per']."</a>";
			if ($anio) echo " <span class=negro>»</span> <a href='plan.php?id_cliente=".$id_cliente."&id_personal=".$id_personal."&anio=".$anio."'>".$anio."</a>";
			if ($mes) echo " <span class=negro>»</span> <a href='plan.php?id_cliente=".$id_cliente."&id_personal=".$id_personal."&anio=".$anio."&mes=".$mes."'>".$nombre[$mes]."</a>";
			if ($id_proyecto) echo " <span class=negro>»</span> pro: <a href='plan.php?id_cliente=".$id_cliente."&id_personal=".$id_personal."&anio=".$anio."&mes=".$mes."&id_proyecto=".$id_proyecto."'>".$p_h['acr_pro']."</a>";
			if ($id_actividad) echo " <span class=negro>»</span> act: <a href='plan.php?id_cliente=".$id_cliente."&id_personal=".$id_personal."&anio=".$anio."&mes=".$mes."&id_proyecto=".$id_proyecto."&id_actividad=".$id_actividad."'>".$p_h['nom_act']."</a>";
			echo "</h2>";*/

			echo "<table width='98%' cellspacing='0' cellpadding='5' class='' style='text-align:center;'>";
			echo "<colgroup width='13%'></colgroup>";
			echo "<colgroup width='6%'></colgroup>";
			echo "<colgroup width='7%'></colgroup>";
			echo "<colgroup width='23%'></colgroup>";
			echo "<colgroup width='22%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='5%'></colgroup>";
			echo "<thead class='th_b'>";
			echo "<tr>";

			echo "	<th>";
			echo "		Recurso";
			echo "	</th>";

			echo "	<th>";
			echo "		Año";
			echo "	</th>";

			echo "	<th>";
			echo "		Mes";
			echo "	</th>";

			echo "	<th>";
			echo "		Proyecto";
			echo "	</th>";

			echo "	<th>";
			echo "		Actividad";
			echo "	</th>";

			echo "	<th>";
			echo "		<abbr title='Horas presentadas'>HP</abbr>";
			echo "	</th>";

			echo "	<th>";
			echo "		<abbr title='Horas aprobadas'>HA</abbr>";
			echo "	</th>";

			echo "	<th>";
			echo "		<abbr title='Horas justificadas'>HJ</abbr>";
			echo "	</th>";

			echo "	<th>";
			echo "		<abbr title='Horas libres'>Libres</abbr>";
			echo "	</th>";

			/*
			echo "	<th class='fondo_blanco sorttable_nosort th_n' width='8%'>";
			echo "		&nbsp;";
			echo "	</th>";
			*/
			echo "</tr>";
			echo "</thead>";
			echo "</table>";

			echo "</div>";

			echo "<div id='cuerpo_tabla' style='min-width:750px;position:absolute;margin-top:100px;width:98%;'>";

			echo "<table width='98%' cellspacing='0' cellpadding='5' class='' style='text-align:center;'>";
			echo "<colgroup width='13%' style='text-align:left;'></colgroup>";
			echo "<colgroup width='6%'></colgroup>";
			echo "<colgroup width='7%'></colgroup>";
			echo "<colgroup width='23%'></colgroup>";
			echo "<colgroup width='22%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='8%'></colgroup>";
			echo "<colgroup width='5%'></colgroup>";
			echo "<tbody>";
			$pers_ant=$p_h['nom_per'];
			$cost_ant=$p_h['coste_h'];
			$anio_ant=$p_h['anio'];
			$mes_ant=$p_h['mes'];

			$total_horas=0;

			$total_horas_aprobadas=0;
			$total_horas_justificadas=0;

			$total_coste=0;
			$total_coste_aprobado=0;
			$total_coste_justificado=0;

			$leyenda=0;

			for ($i=0;$i<$num_datos;$i++) {
				echo "<tr class='seleccionable' onmouseover='document.getElementById(\"id".$p_h['id_personal']."_".$p_h['anio']."_".$p_h['mes']."_".$p_h['id_actividad']."\").style.opacity=\"1\"' onmouseout='document.getElementById(\"id".$p_h['id_personal']."_".$p_h['anio']."_".$p_h['mes']."_".$p_h['id_actividad']."\").style.opacity=\"0.2\"'>";

				echo "<td class='tsep' onmouseover='this.lastChild.style.display=\"\"' onmouseout='this.lastChild.style.display=\"none\"'>";
				if ($pers_ant!=$p_h['nom_per'] || $cost_ant!=$p_h['coste_h'] || $i==0) {
					if(!$id_personal) echo "<a href='javascript:;' onclick='if(location.search.indexOf(\"id_personal\")==-1) location.search+=\"&id_personal=".$p_h['id_personal']."\"'>";
					echo $p_h['nom_per'];
					if(!$id_personal) echo "</a>";
					echo " <br>(".$p_h['coste_h']." &euro;)";
				}
				else echo "&uarr;";
				echo "<div style='display:none;position:absolute;background-color:black;color:white;opacity:0.8;padding:2px;border-radius:5px;'>".$p_h['nom_per']."</div>";
				echo "</td>";

				echo "<td class='tsep' sorttable_customkey='".$p_h['anio']."' onmouseover='this.lastChild.style.display=\"\"' onmouseout='this.lastChild.style.display=\"none\"'>";
				if ($anio_ant!=$p_h['anio'] || $i==0) {
					if(!$anio) echo "<a href='javascript:;' onclick='if(location.search.indexOf(\"anio\")==-1) location.search+=\"&anio=".$p_h['anio']."\"'>";
					echo $p_h['anio'];
					if(!$anio) echo "</a>";
				}
				else echo "&uarr;";
				echo "<div style='display:none;position:absolute;background-color:black;color:white;opacity:0.8;padding:2px;border-radius:5px;'>".$p_h['anio']."</div>";
				echo "</td>";


				echo "<td class='tsep' sorttable_customkey='".$p_h['mes']."' onmouseover='this.lastChild.style.display=\"\"' onmouseout='this.lastChild.style.display=\"none\"'>";
				if ($mes_ant!=$p_h['mes'] || $i==0) {
					if(!$mes) echo "<a href='javascript:;' onclick='if(location.search.indexOf(\"mes\")==-1) location.search+=\"&mes=".$p_h['mes']."\"'>";
					echo $nombre[$p_h['mes']];
					if(!$mes) echo "</a>";
				}
				else echo "&uarr;";
				echo "<div style='display:none;position:absolute;background-color:black;color:white;opacity:0.8;padding:2px;border-radius:5px;'>".$nombre[$p_h['mes']]." ".$p_h['anio']."</div>";
				echo "</td>";

				echo "<td class='tsep'>";
				if(!$id_proyecto) echo  "<a href='javascript:;' onclick='if(location.search.indexOf(\"id_proyecto\")==-1) location.search+=\"&id_proyecto=".$p_h['id_pro']."\"'>";
				echo $p_h['acr_pro'];
				if($num_clientes>1) {
					echo " <i>(".getCliFromPro($p_h['id_pro']).")</i>";
				}
				if(!$id_proyecto) echo "</a>";
				echo "</td>";

				echo "<td class='tsep'>";
				if(!$id_actividad) echo  "<a href='javascript:;' onclick='if(location.search.indexOf(\"id_actividad\")==-1) location.search+=\"&id_actividad=".$p_h['id_actividad']."\"'>";
				echo $p_h['nom_act'];
				if(!$id_actividad) echo "</a>";
				echo "</td>";

				$horas_libres=horasLibres($p_h['id_personal'],$p_h['anio'],$p_h['mes']);

				//----------------------------------->
				
				echo "<td class='tsep'>";
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $nivel==5) {
					if($horas_libres<0) echo "<span class=rojo>";
					else if($horas_libres>0) echo "<span class=verde>";
					echo "<a style='text-decoration:none;' href='javascript:;' onclick='cambiar_horas(".$p_h['id_personal'].",".$p_h['id_actividad'].",".$p_h['anio'].",".$p_h['mes'].",".$p_h['horas'].",\"0\")'>";
				}
				echo $p_h['horas'];
				/*if($horas_libres!=0){
					if ($horas_libres>=0) echo "&nbsp;(+&nbsp;";
					else echo "&nbsp;(-&nbsp;";
					echo abs($horas_libres)."&nbsp;=&nbsp;".number_format($p_h['horas']+$horas_libres).")";
				}*/
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $nivel==5) {
					echo "</a>";
					if($horas_libres!=0) echo "</span>";
				}
				else if($leyenda==0) $leyenda=1;
				echo "</td>";


				
				echo "<td class='tsep'>";
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $p_h['est_pro']=="PRESENTADO" || $nivel==5) {
					if($horas_libres<0) echo "<span class=rojo>";
					else if($horas_libres>0) echo "<span class=verde>";
					echo "<a style='text-decoration:none;' href='javascript:;' onclick='cambiar_horas(".$p_h['id_personal'].",".$p_h['id_actividad'].",".$p_h['anio'].",".$p_h['mes'].",".$p_h['horas_aprobadas'].",\"1\")'>";
				}
				echo $p_h['horas_aprobadas'];
				/*if($horas_libres!=0){
					if ($horas_libres>=0) echo "&nbsp;(+&nbsp;";
					else echo "&nbsp;(-&nbsp;";
					echo abs($horas_libres)."&nbsp;=&nbsp;".number_format($p_h['horas_aprobadas']+$horas_libres).")";
				}*/
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $p_h['est_pro']=="PRESENTADO" || $nivel==5) {
					echo "</a>";
					if($horas_libres!=0) echo "</span>";
				}
				else if($leyenda==0) $leyenda=1;
				echo "</td>";


				
				echo "<td class='tsep'>";
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $p_h['est_pro']=="PRESENTADO" || $p_h['est_pro']=="APROBADO" || $nivel==5) {
					if($horas_libres<0) echo "<span class=rojo>";
					else if($horas_libres>0) echo "<span class=verde>";
					echo "<a style='text-decoration:none;' href='javascript:;' onclick='cambiar_horas(".$p_h['id_personal'].",".$p_h['id_actividad'].",".$p_h['anio'].",".$p_h['mes'].",".$p_h['horas_justificadas'].",\"2\")'>";
				}
				echo $p_h['horas_justificadas'];
				/*if($horas_libres!=0){
					if ($horas_libres>=0) echo "&nbsp;(+&nbsp;";
					else echo "&nbsp;(-&nbsp;";
					echo abs($horas_libres)."&nbsp;=&nbsp;".number_format($p_h['horas_justificadas']+$horas_libres).")";
				}*/
				if($p_h['est_pro']=="EN CURSO" || $p_h['est_pro']=="SIN EMPEZAR" || $p_h['est_pro']=="PRESENTADO" || $p_h['est_pro']=="APROBADO" || $nivel==5) {
					echo "</a>";
					if($horas_libres!=0) echo "</span>";
				}
				else if($leyenda==0) $leyenda=1;
				echo "</td>";

				//-------------------------------------<

				$horas_libres=horasLibres($p_h['id_personal'],$p_h['anio'],$p_h['mes']);
				echo "<td class='tsep'>";
				if($horas_libres<0) echo "<span class=rojo><a style='text-decoration:none;' href='plan.php?id_personal=".$p_h['id_personal']."&anio=".$p_h['anio']."&mes=".$p_h['mes']."'>";
				echo horasLibres($p_h['id_personal'],$p_h['anio'],$p_h['mes']);
				if($horas_libres<0) echo " ?</a> </span>";
				echo "</td>";

				/*
				echo "<td class='fondo_blanco' style='opacity:0.2;' id='id".$p_h['id_personal']."_".$p_h['anio']."_".$p_h['mes']."_".$p_h['id_actividad']."'>";
				echo "<img src='imagenes/borrar.gif' width='16px' height='16px'>";
				echo "</td>";
				*/
				echo "</tr>";
				$pers_ant=$p_h['nom_per'];
				$cost_ant=$p_h['coste_h'];
				$anio_ant=$p_h['anio'];
				$mes_ant=$p_h['mes'];

				$total_horas+=$p_h['horas'];
				$total_horas_aprobadas+=$p_h['horas_aprobadas'];
				$total_horas_justificadas+=$p_h['horas_justificadas'];

				$total_coste+=($p_h['horas']*$p_h['coste_h']);
				$total_coste_aprobado+=($p_h['horas_aprobadas']*$p_h['coste_h']);
				$total_coste_justificado+=($p_h['horas_justificadas']*$p_h['coste_h']);

				$p_h=next($personal_horas);
			}
		?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5">&nbsp;</td>
					<td bgcolor='#eeeeee'><?php echo $total_horas."&nbsp;h"; if($id_proyecto) echo "<br>".number_format($total_coste,2,',','.')."&nbsp;€"; ?></td>
					<td bgcolor='#eeeeee'><?php echo $total_horas_aprobadas."&nbsp;h"; if($id_proyecto) echo "<br>".number_format($total_coste_aprobado,2,',','.')."&nbsp;€"; ?></td>
					<td bgcolor='#eeeeee'><?php echo $total_horas_justificadas."&nbsp;h"; if($id_proyecto) echo "<br>".number_format($total_coste_justificado,2,',','.')."&nbsp;€"; ?></td>
					<td>&nbsp;</td>
				</tr>
			</tfoot>
		</table>
		<?
		if($advertir==1)
				echo "<br><span class='msj_centrado_s'><b>¡No se encontró ningún dato!</b></span><br><br><span class='msj_centrado_s'>Puede volver atrás pulsando el botón 'página anterior' de su navegador. Además, puede ayudarse de las opciones de filtrado de la parte superior de la página.</span>";
				if($leyenda==1) echo "<p>Los horas en negro no son modificables</p>";
		?>
		
		</div><!--de cuerpo_tabla-->
	</div>
	<?php
	} else { ?>
		<script type="text/javascript">
			location.href="index.php?ref="+encodeURIComponent(location.href);
		</script>
	<?php } ?>
</div>
</body>
</html>
