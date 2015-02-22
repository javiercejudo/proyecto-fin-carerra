<?php
function esFestivo ($dia,$mes) {
	if ($mes == 1  && ($dia == 1 || $dia == 6)) return true;
	if ($mes == 5  &&  $dia == 1)  return true;
	if ($mes == 10 &&  $dia == 12) return true;
	if ($mes == 11 &&  $dia == 1)  return true;
	if ($mes == 12 && ($dia == 6 || $dia == 8 || $dia == 25)) return true;
	return false;
}

function laborables($f_ini_uni,$f_fin_uni) {
	$current_date = $f_ini_uni;
	$num = 0;
	while ($current_date <= $f_fin_uni) {
		$dia = date('w',$current_date);
		if ($dia > 0 && $dia < 6 && !esFestivo(date('j',$current_date),date('n',$current_date)))
			$num++;
		//$current_date = mktime(0,0,0,date('m',$current_date),date('d',$current_date)+1,date('Y',$current_date));
		$current_date += 86400;
	}
	return $num;
}

function coincide ($a,$b) {
	if(abs($a - $b) < 0.01)
		return "<span style='color:green;font-weight:bold;'>Sí!</span>";
	return "<span style='color:red;font-weight:bold;'>No!</span>";
}

echo "<h1>Test del algoritmo de distribucion de horas</h1>\n";
echo "<h2>creado por Javier Cejudo</h2>\n";

$errores = 0;
$numero_iteraciones = 50;

for($i=1; $i <= $numero_iteraciones; $i++) {
	$mes_inicio = rand(1,12);
	$anio_inicio = rand(2010,2025);
	$dia_inicio = rand(1,date('t',mktime(0,0,0,$mes_inicio,1,$anio_inicio)));
	$fecha_inicio = mktime(0,0,0,$mes_inicio,$dia_inicio,$anio_inicio);
	$fecha_fin = mktime(0,0,0,$mes_inicio,$dia_inicio + rand(0,800),$anio_inicio);
	$duracion_en_dias = laborables($fecha_inicio,$fecha_fin);
	$duracion_en_horas = $duracion_en_dias * rand(1,900) / 100;
	
	echo "<h3>Datos de la prueba numero $i:</h3>\n";
	echo "Fecha de inicio: " . date('d/m/Y',$fecha_inicio) . "<br>\n";
	echo "Fecha de fin: " . date('d/m/Y',$fecha_fin) . "<br>\n";
	echo "Duracion en dias: " . $duracion_en_dias . "<br>\n";
	echo "Horas limite: " . ($duracion_en_dias * 8) . "<br>\n";
	
	$horas_dia = $duracion_en_horas / $duracion_en_dias;
	if($duracion_en_horas > $duracion_en_dias * 8) {
		$duracion_en_horas = $duracion_en_dias * 8;
		$horas_dia = 8;
	}	
	$duracion_en_horas_original = $duracion_en_horas;
		
	echo "Duracion en horas: " . $duracion_en_horas . "<br>\n";
	echo "Horas por dia: " . $horas_dia . "<br>\n";

	$num_anios = date('Y',$fecha_fin) - date('Y',$fecha_inicio);
	$num_meses_completos = ($num_anios * 12) + (date('n',$fecha_fin) - date('n',$fecha_inicio)) - 1;
	echo "Anios involucrados: " . ($num_anios+1) . "<br>\n";
	echo "Numero meses intermedios: " . $num_meses_completos . "<br>\n";
	
	$horas_asignadas = 0;
	
	if ($num_meses_completos == -1){
		$horas_aux = $duracion_en_horas;
		if($horas_aux > $duracion_en_dias * 8)
			$horas_aux = $duracion_en_dias * 8;
		$horas_asignadas += $horas_aux;
	} else {
		$laborables=laborables($fecha_inicio,mktime(0,0,0,date('n',$fecha_inicio)+1,0,date('Y',$fecha_inicio)));
		$horas_aux = $horas_dia * $laborables;
		if($horas_aux > 5)
			$horas_aux = ceil($horas_aux / 5) * 5;
		else
			$horas_aux = ceil($horas_aux);
		if($horas_aux > $laborables * 8)
			$horas_aux = $laborables*8;
		if($duracion_en_horas < $horas_aux)
			$horas_aux = $duracion_en_horas;
		$duracion_en_horas -= $horas_aux;
		$horas_asignadas += $horas_aux;
		
		for ($mes_actual = date('n',$fecha_inicio); $mes_actual < date('n',$fecha_inicio) + $num_meses_completos; $mes_actual++) {
			$fecha_aux = mktime(0,0,0,$mes_actual + 1,1,date('Y',$fecha_inicio));
			$laborables = laborables($fecha_aux,mktime(0,0,0,$mes_actual + 2,0,date('Y',$fecha_inicio)));
			$horas_aux = $horas_dia * $laborables;
			if($horas_aux > 5) {
				$horas_aux = ceil($horas_aux / 5) * 5;
			} else {
				$horas_aux = ceil($horas_aux);
			}
			if($horas_aux > $laborables * 8)
				$horas_aux = $laborables * 8;
			if($duracion_en_horas < $horas_aux)
				$horas_aux = $duracion_en_horas;
			$duracion_en_horas -= $horas_aux;
			$horas_asignadas += $horas_aux;
		}
		
		$laborables=laborables(mktime(0,0,0,date('n',$fecha_fin),1,date('Y',$fecha_fin)),$fecha_fin);
		$horas_aux = $duracion_en_horas;
		if($horas_aux > $laborables * 8)
			$horas_aux = $laborables*8;
		$horas_asignadas += $horas_aux;
	}
	
	echo "Horas asignadas: " . $horas_asignadas . " <br>\n";
	echo "Exito?: " . coincide($duracion_en_horas_original,
$horas_asignadas) . "<br>\n";
	if(($duracion_en_horas_original - $horas_asignadas) > 0.01) {
		$errores++;
		echo "Diferencia: " . abs($duracion_en_horas_original - $horas_asignadas) . "<br>\n";
	}
}

echo "<h1>-------------------------------------------</h1>\n";
echo "<h1>Resultados</h1>\n";

echo "Errores: " . $errores . " <br>\n";
echo "Tasa de errores: " . ($errores / $numero_iteraciones) * 100 . "% \n";
	
?>
