<?php
	include ("include/sesion.php");
	
	//Eliminamos Cache del Navegador
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<title>Resumen de personal asignado a proyectos | Ingeniería e Innovación | Consultora especializada en la gestión de la I+D+i</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="keywords" content="ingeniería,innovación,Ingeniería e Innovación,consultora,I+D+i,innova">
	<meta name="description" content="Ingenier&iacute;a e Innovaci&oacute;n es una consultora especializada en la gesti&oacute;n de la I+D+i cuya actividad principal es el asesoramiento especializado a las empresas en la gesti&oacute;n de la I+D+i.">
	<link rel="stylesheet" type="text/css" href="include/estilos.css">
	<link rel="shortcut icon" href="favicon.ico">
	<script src="include/sorttable.js" type="text/javascript"></script>
	<style type="text/css">
		/*td a {color:black;}*/
	</style>
</head>

<body onkeydown="tecla(event);">
<?php include ("header.php"); ?>
<div id="cuerpo">
	<?php
	if ($nivel>2){
		echo "<div id='menu'>";
		include ('menu.php');
		echo "</div>";

		$id_cliente=$_REQUEST['id_cliente'];
		
		echo "<div id='contenido'>";//<div id='resumen_proyectos'>
		if($id_cliente && getNomCli($id_cliente)!=false) {
			$nomCli = getNomCli($id_cliente);
			$acrCli = getAcrCli($id_cliente);
			$numEmp = numEmployees($id_cliente);
			if (numProjects($id_cliente)>0) {
				if (numEmployees($id_cliente)>0){
					$min_anio = minAnio($id_cliente);
					$max_anio = maxAnio($id_cliente);
					$k=$min_anio;
					
					echo "<h1 class='rojo'>Resumen de personal asignado a proyectos de ".$nomCli." (".$acrCli.")</h1>";
					echo "<div class='esp_abajo'><a class='rojo' href='buscar_personal.php?id_cliente=".$id_cliente."'>Ver personal</a> | <a class='rojo' href='act_buscar.php?id_cliente=".$id_cliente."'>Ver actividades</a></div>";
					echo "<h2 class='rojo esp_abajo'>Predicción de horas asignadas</h2>";
					$table = "<table width='100%' cellpadding='2' cellspacing='0' class='sortable'>";
					$table .= "<thead class='th_b'>";
					
					$table .= "<tr>";
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					
					while($k<=$max_anio){
						//************************************
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects);
						for ($i=0;$i<$numPro;$i++) {
							$table .= "<th bgcolor='".colorEstadoProyecto2($projects[$i]['estado'])."'>".$projects[$i]['acronimo']."</th>";
							///else $table .= "<th bgcolor='#ddd'>".$projects[$i]['acronimo']."</th>";
							//$table .= "<th>".$projects[$i]['acronimo']."</th>";
						}
						//************************************
						$table .= "<th bgcolor='#eeeeee' width='30'>Total&nbsp;/&nbsp;Libres ".$k."</th>";
						//else $table .= "<th bgcolor='#ddd'>Total<br>".$k."</th>";
						$k++;
					}
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					$table .= "</tr>";
					$table .= "</thead>";
					$table .= "<tbody>";
					for($i=0;$i<$numEmp;$i++){
						$table .= "<tr class='seleccionable'>";
						$table .= "<td class='tsep' style='text-align:center;'>";
						$empleado = getIdNomEmployees($id_cliente,$i);
						$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
						$table .= "</td>";
						$k=$min_anio;
						while($k<=$max_anio){
							//**********************principio12
							$projects=getIdNomProjectsAnio($id_cliente,$k);
							$numPro = count($projects);
							for ($l=0;$l<$numPro;$l++) {
								$total_ocupadas = horasOcupadasProyectoAnio($empleado[0],$k,$projects[$l]['id']);
								if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
									if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
										if($i % 2 ==0) $table .= "<td bgcolor='".colorEstadoProyecto($projects[$l]['estado'])."' class='tsep' style='text-align:center;'>";
										else $table .= "<td bgcolor='".colorEstadoProyecto2($projects[$l]['estado'])."' class='tsep' style='text-align:center;'>";
										if($total_ocupadas!=0)
											$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
										$table .= $total_ocupadas;
									} else {										if($i % 2 ==0) $table .= "<td bgcolor='".colorEstadoProyecto($projects[$l]['estado'])."' class='tsep' style='text-align:center;' sorttable_customkey='-1'>";
										else $table .= "<td bgcolor='".colorEstadoProyecto2($projects[$l]['estado'])."' class='tsep' style='text-align:center;' sorttable_customkey='-1'>";
										$table .= "-";
									}
								} else {
									if($i % 2 ==0) $table .= "<td bgcolor='".colorEstadoProyecto($projects[$l]['estado'])."' class='tsep' style='text-align:center;' sorttable_customkey='".$total_ocupadas."'>";
									else $table .= "<td bgcolor='".colorEstadoProyecto2($projects[$l]['estado'])."' class='tsep' style='text-align:center;' sorttable_customkey='".$total_ocupadas."'>";
									if($total_ocupadas!=0)
											$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
									$table .= $total_ocupadas."<sup>X</sup>";
								}
								$table .= "</td>";
							}
							//**********************fin12
							$total_ocupadas = 0;
							for ($j=0;$j<12;$j++) {
								$total_ocupadas += horasOcupadas($empleado[0],$k,$j+1);
							}
							$style = "";
							if ($total_ocupadas > horasAnioPersonal ($empleado[0],$k))
								$style .= "rojo";
							if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
								if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
									$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
									if($total_ocupadas!=0)
										$table .= "<a class='".$style."' target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
									$table .= $total_ocupadas."&nbsp;/&nbsp;".(horasAnioPersonal($empleado[0],$k)-$total_ocupadas);
								} else {
									$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='-1'>";
									$table .= "-";
								}
							} else {
								$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
								if($total_ocupadas!=0)
									$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
								$table .= $total_ocupadas."<sup>X</sup>";
							}
							if($total_ocupadas!=0) 
								$table .= "</a>";
								$table .= "</td>";
							$k++;
						}
						$table .= "<td class='tsep' style='text-align:center;'>";
						$empleado = getIdNomEmployees($id_cliente,$i);
						$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
						$table .= "</td>";
						$table .= "</tr>";
					}
					$table .= "</tbody>";
					$table .= "<tfoot>";					
					$table .= "<tr>";
					$table .= "<td>";
					$table .= "&nbsp;";
					$table .= "</td>";
					$k=$min_anio;
					while($k<=$max_anio){
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects)+1;
						$table .= "<td colspan='".$numPro."' style='text-align:center;background-color:#eee;border-right:1px solid #999;'>";
						$table .= "año ".$k;
						$table .= "</td>";
						$k++;
					}
					$table .= "</tr>";
					$table .= "</tfoot>";
					$table .= "</table>";
					$table .= "<br><table cellpadding='2' cellspacing='5'><tr><td bgcolor='".colorEstadoProyecto2('EN CURSO')."'>Sin empezar, En curso y Presentado</td><td bgcolor='".colorEstadoProyecto2('APROBADO')."'>Aprobado</td><td bgcolor='".colorEstadoProyecto2('CONCLUIDO')."'>Justificado, Concluido</td><td bgcolor='".colorEstadoProyecto2('DENEGADO')."'>Denegado</td><td><a href='resumen_proyectos_xls.php?id_cliente=".$id_cliente."'><img class='img_abajo' src='imagenes/excel.jpg'> Descargar en XLS</a></td></tr></table>";
					echo $table;
					
					

					echo "<br>";
					
					

					echo "<h2 class='rojo esp_abajo'>En propuestas presentadas</h2>";
					$table = "<table width='100%' cellpadding='2' cellspacing='0' class='sortable'>";
					$table .= "<thead class='th_b'>";
					$table .= "<tr>";
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					$min_anio = minAnio($id_cliente);
					$max_anio = maxAnio($id_cliente);
					$k=$min_anio;
					while($k<=$max_anio){
						//************************************
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects);
						for ($i=0;$i<$numPro;$i++) {
							$table .= "<th>".$projects[$i]['acronimo']."</th>";
						}
						//************************************
						$table .= "<th bgcolor='#eeeeee' width='30'>Total&nbsp;/&nbsp;Libres ".$k."</th>";
						$k++;
					}
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					$table .= "</tr>";
					$table .= "</thead>";
					$table .= "<tbody>";
					for($i=0;$i<$numEmp;$i++){
						$table .= "<tr class='seleccionable'>";
							$table .= "<td class='tsep' style='text-align:center;'>";
								$empleado = getIdNomEmployees($id_cliente,$i);
								$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
							$table .= "</td>";
							$k=$min_anio;
							while($k<=$max_anio){
								//**********************principio22
								$projects=getIdNomProjectsAnio($id_cliente,$k);
								$numPro = count($projects);
								for ($l=0;$l<$numPro;$l++) {
									$total_ocupadas = horasPresentadasProyectoAnio($empleado[0],$k,$projects[$l]['id']);
									if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
										if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
											$table .= "<td class='tsep' style='text-align:center;'>";
											if($total_ocupadas!=0)
												$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
											$table .= $total_ocupadas;
										} else {											$table .= "<td class='tsep' style='text-align:center;' sorttable_customkey='-1'>";
											$table .= "-";
										}
									} else {
										$table .= "<td class='tsep' style='text-align:center;' sorttable_customkey='".$total_ocupadas."'>";
										if($total_ocupadas!=0)
											$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
										$table .= $total_ocupadas."<sup>X</sup>";
									}
									$table .= "</td>";
								}
								//**********************fin22
								$total_ocupadas = 0;
								for ($j=0;$j<12;$j++) {
									$total_ocupadas += horasPresentadas($empleado[0],$k,$j+1);
								}
								$style = "";
								if ($total_ocupadas > horasAnioPersonal ($empleado[0],$k))
									$style .= "rojo";
								if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
									if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
										$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
										if($total_ocupadas!=0)
											$table .= "<a class=".$style." target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
										$table .= $total_ocupadas."&nbsp;/&nbsp;".(horasAnioPersonal($empleado[0],$k)-$total_ocupadas);
									} else {
										$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='-1'>";
										$table .= "-";
									}
								} else {
									$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
									if($total_ocupadas!=0)
										$table .= "<a class=".$style." target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
									$table .= $total_ocupadas."<sup>X</sup>";
								}
								if($total_ocupadas!=0) 
									$table .= "</a>";
									$table .= "</td>";
								$k++;
							}
						$table .= "<td class='tsep' style='text-align:center;'>";
						$empleado = getIdNomEmployees($id_cliente,$i);
						$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
						$table .= "</td>";
						$table .= "</tr>";
					}
					$table .= "</tbody>";
					$table .= "<tfoot>";					
					$table .= "<tr>";
					$table .= "<td>";
					$table .= "&nbsp;";
					$table .= "</td>";
					$k=$min_anio;
					while($k<=$max_anio){
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects)+1;
						$table .= "<td colspan='".$numPro."' style='text-align:center;background-color:#eee;border-right:1px solid #999;'>";
						$table .= "año ".$k;
						$table .= "</td>";
						$k++;
					}
					$table .= "</tr>";
					$table .= "</tfoot>";
					$table .= "</table>";
					echo $table;
					
					

					echo "<br>";
					
					

					echo "<h2 class='rojo esp_abajo'>En propuestas justificadas</h2>";
					$table = "<table width='100%' cellpadding='2' cellspacing='0' class='sortable'>";
					$table .= "<thead class='th_b'>";
					$table .= "<tr>";
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					$min_anio = minAnio($id_cliente);
					$max_anio = maxAnio($id_cliente);
					$k=$min_anio;
					while($k<=$max_anio){
						//************************************
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects);
						for ($i=0;$i<$numPro;$i++) {
							$table .= "<th>".$projects[$i]['acronimo']."</th>";
						}
						//************************************
						$table .= "<th bgcolor='#eeeeee' width='30'>Total&nbsp;/&nbsp;Libres ".$k."</th>";
						$k++;
					}
					$table .= "<th width='250'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					$table .= "</tr>";
					$table .= "</thead>";
					$table .= "<tbody>";
					for($i=0;$i<$numEmp;$i++){
						$table .= "<tr class='seleccionable'>";
							$table .= "<td class='tsep' style='text-align:center;'>";
								$empleado = getIdNomEmployees($id_cliente,$i);
								$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
							$table .= "</td>";
							$k=$min_anio;
							while($k<=$max_anio){
								//**********************principio32
								$projects=getIdNomProjectsAnio($id_cliente,$k);
								$numPro = count($projects);
								for ($l=0;$l<$numPro;$l++) {
									$total_ocupadas = horasJustificadasProyectoAnio($empleado[0],$k,$projects[$l]['id']);
									if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
										if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
											$table .= "<td class='tsep' style='text-align:center;'>";
											if($total_ocupadas!=0)
												$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
											$table .= $total_ocupadas;
										} else {											$table .= "<td class='tsep' style='text-align:center;' sorttable_customkey='-1'>";
											$table .= "-";
										}
									} else {
										$table .= "<td class='tsep' style='text-align:center;' sorttable_customkey='".$total_ocupadas."'>";
										if($total_ocupadas!=0)
											$table .= "<a target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."&id_proyecto=".$projects[$l]['id']."'>";
										$table .= $total_ocupadas."<sup>X</sup>";
									}
									$table .= "</td>";
								}
								//**********************fin32
								$total_ocupadas = 0;
								for ($j=0;$j<12;$j++) {
									$total_ocupadas += horasJustificadas($empleado[0],$k,$j+1);
								}
								$style = "";
								if ($total_ocupadas > horasAnioPersonal ($empleado[0],$k))
									$style .= "rojo";
								if (sigueEnPlantillaI($empleado[0],$k) && sigueEnPlantillaD($empleado[0],$k)) {
									if (dadoDeAltaI($empleado[0],$k) && dadoDeAltaD($empleado[0],$k)) {
										$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
										if($total_ocupadas!=0)
											$table .= "<a class=".$style." target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
										$table .= $total_ocupadas."&nbsp;/&nbsp;".(horasAnioPersonal($empleado[0],$k)-$total_ocupadas);
									} else {
										$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='-1'>";
										$table .= "-";
									}
								} else {
									$table .= "<td class='tsep' style='text-align:center;background-color:#eee;border-right:1px solid #999;' sorttable_customkey='".$total_ocupadas."'>";
									if($total_ocupadas!=0)
										$table .= "<a class=".$style." target='_blank' href='plan.php?id_personal=".$empleado[0]."&anio=".$k."'>";
									$table .= $total_ocupadas."<sup>X</sup>";
								}
								if($total_ocupadas!=0) 
									$table .= "</a>";
									$table .= "</td>";
								$k++;
							}
						$table .= "<td class='tsep' style='text-align:center;'>";
						$empleado = getIdNomEmployees($id_cliente,$i);
						$table .= "<a target='_blank' href='buscar_personal.php?id=".$empleado[0]."'>".ucwords(strtolower($empleado[1]))."</a>";
						$table .= "</td>";
						$table .= "</tr>";
					}
					$table .= "</tbody>";
					$table .= "<tfoot>";					
					$table .= "<tr>";
					$table .= "<td>";
					$table .= "&nbsp;";
					$table .= "</td>";
					$k=$min_anio;
					while($k<=$max_anio){
						$projects=getIdNomProjectsAnio($id_cliente,$k);
						$numPro = count($projects)+1;
						$table .= "<td colspan='".$numPro."' style='text-align:center;background-color:#eee;border-right:1px solid #999;'>";
						$table .= "año ".$k;
						$table .= "</td>";
						$k++;
					}
					$table .= "</tr>";
					$table .= "</tfoot>";
					$table .= "</table>";
					echo $table;
				} else {
						echo "<p>".$nomCli." (".$acrCli.") no tiene ningún empleado.</p>";
				}
			} else {
				echo "<p>".$nomCli." (".$acrCli.") no tiene ningún proyecto.</p>";
			}
		} else {
			echo "<p>Debe seleccionar un cliente válido.</p>";
		}
		echo "</div>";//id='resumen_proyectos'
	} else { ?>
		<script type="text/javascript">
			location.href="index.php?ref="+encodeURIComponent(location.href);
		</script>
	<?php } ?>
</div>
</body>
</html>
