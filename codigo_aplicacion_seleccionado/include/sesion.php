<?php
	session_name('ingenieria');
	session_start();
	include ('mysql.php');
	//Recogemos variables de Sesion
	
	if (isset($_COOKIE['usuario'])) {
		$usuario=$_COOKIE['usuario'];
		$usuarioid=$_COOKIE['usuarioid'];
		$nivel=$_COOKIE['nivel'];
		$nombreusu=$_COOKIE['nombreusu'];
		
		$_SESSION['usuario']=$_COOKIE['usuario'];
		$_SESSION['usuarioid']=$_COOKIE['usuarioid'];
		$_SESSION['nivel']=$_COOKIE['nivel'];
		$_SESSION['nombreusu']=$_COOKIE['nombreusu'];
	} else {
		if (isset($_SESSION['usuario'])){
			$usuario=$_SESSION['usuario'];
			$usuarioid=$_SESSION['usuarioid'];
			$nivel=$_SESSION['nivel'];
			$nombreusu=$_SESSION['nombreusu'];
		} else {
			$usuario="invitado";
			$_SESSION['usuario']=$usuario;
			$_SESSION['usuarioid']=0;
			$_SESSION['nivel']=0;
		}
	}
  //foreach($_SESSION as $k => $v) {
  //echo $k." => ".$v."<br>";
  //}
  //require ('variables.php');
  
  //Variables Globales
	$iconos=array("zip"=>"imagenes/zip.jpg","carpeta"=>"imagenes/carpeta.jpg","xls"=>"imagenes/excel.jpg","pdf"=>"imagenes/pdf.jpg","doc"=>"imagenes/word.jpg","mdb"=>"imagenes/access.jpg","rar"=>"imagenes/rar.jpg","jpg"=>"imagenes/imagen.jpg","png"=>"imagenes/imagen.jpg","gif"=>"imagenes/imagen.jpg","tif"=>"imagenes/tiff.jpg","dwg"=>"imagenes/acad.jpg");
	$tip=$db->db_query("SELECT * FROM tipo_expe ORDER BY id",true);
	$num=count($tip);
	if($num>0) $tipo=reset($tip);
	for ($i=1;$i<=$num;$i++){
		$tipexpe[$tipo['id']]=$tipo['id'];
		$tipo=next($tip);
	}
	$tip=$db->db_query("SELECT * FROM sector ORDER BY sectores",true);
	$num=count($tip);
	if($num>0) $tipo=reset($tip);
	for ($i=1;$i<=$num;$i++){
		$sector[$tipo['sectores']]=$tipo['sectores'];
		$tipo=next($tip);
	}
	$semana=array("1"=>"Lunes","2"=>"Martes","3"=>"Miercoles","4"=>"Jueves","5"=>"Viernes","6"=>"Sabado","7"=>"Domingo");
	$meses=array("1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre");
	$meses_cortos=array("1"=>"E","2"=>"F","3"=>"M","4"=>"A","5"=>"M","6"=>"J","7"=>"J","8"=>"A","9"=>"S","10"=>"O","11"=>"N","12"=>"D");
	$meses_medios=array("1"=>"ENE","2"=>"FEB","3"=>"MAR","4"=>"ABR","5"=>"MAY","6"=>"JUN","7"=>"JUL","8"=>"AGO","9"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DIC");
	$var=$db->db_query("SELECT * FROM variables",true);
	if($num>0) $va=reset($var);
	$num_var=count($var);
	for ($i=1;$i<=$num_var;$i++){
		$variables[$va['nombre']]=$va['valor'];
		//echo "Variable con nombre: ".$va['nombre']." es: ".$variables[$va['nombre']]." deberia tener el valor: ".$va['valor']."<br>";
		$va=next($var);
	}
	
	
/*FUNCIONES RELACIONADAS CON PERSONAL/ACTIVIDADES**************/
/*FUNCIONES RELACIONADAS CON PERSONAL/ACTIVIDADES**************/

function dadoDeAltaD ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("select max(anio) as max_anio from coste_personal cp where id_personal=".$id,true);
	$answer = reset($answer);
	if ($answer['max_anio']==0) return true;
	return ($answer['max_anio'] >= $anio);
}

function dadoDeAltaI ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("select min(anio) as min_anio from coste_personal cp where id_personal=".$id,true);
	$answer = reset($answer);
	if ($answer['min_anio']==0) return true;
	return ($answer['min_anio'] <= $anio);
}

function getIdNomEmployeesProyecto ($id,$num) {
	$answer=$GLOBALS['db']->db_query("select ph.id_personal as idEmp, p.nombre as nomEmp from personal p join personal_horas ph on p.id=ph.id_personal join actividad ac on ph.id_actividad=ac.id where ac.proyecto=$id group by ph.id_personal order by p.nombre limit $num,1",true);
	$answer = reset($answer);
	$res[]=$answer['idEmp'];
	$res[]=$answer['nomEmp'];
	return $res;
}

function numAniosProyecto ($id) {
	$answer=$GLOBALS['db']->db_query("select count(distinct(anio)) as num from personal_horas ph join actividad ac on ph.id_actividad=ac.id where ac.proyecto=$id",true);
	return $answer[0]['num'];
}

function numActividadesProyecto ($id) {
	$answer=$GLOBALS['db']->db_query("select count(*) as num from actividad where proyecto=$id",true);
	return $answer[0]['num'];
}

function getIdNomActividadesProyecto ($id,$num) {
	$answer=$GLOBALS['db']->db_query("select id, nombre from actividad where proyecto=$id order by f_ini limit $num,1",true);
	$answer = reset($answer);
	$res[]=$answer['id'];
	$res[]=$answer['nombre'];
	return $res;
}

function horasAnioPersonal ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("select horas_anio from coste_personal where id_personal=".$id." and anio=".$anio,true);
	return $answer[0]['horas_anio'];
}

function getIdNomProjectsAnio ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("
		(select p.id,p.proyecto,p.acronimo,p.estado from proyectos p join 
		actividad a on p.id=a.proyecto join personal_horas ph on 
		a.id=ph.id_actividad where cliente=".$id." and ph.anio=".$anio." group by p.id 
		order by p.id)
		union
		(select p.id,p.proyecto,p.acronimo,p.estado from proyectos p join
		cooperacion c on p.id=c.proyecto join actividad a on
		c.proyecto=a.proyecto join personal_horas ph on a.id=ph.id_actividad
		where c.cliente=".$id." and ph.anio=".$anio." group by c.proyecto order by
		c.proyecto)
		",true);
	return $answer;
}


function minAnio ($id) {
	$answer=$GLOBALS['db']->db_query("select min(anio) as min_anio from coste_personal ch join personal p on p.id=ch.id_personal where p.id_cliente=".$id,true);
	return $answer[0]['min_anio'];
}

function maxAnio ($id) {
	$answer=$GLOBALS['db']->db_query("select max(anio) as max_anio from coste_personal ch join personal p on p.id=ch.id_personal where p.id_cliente=".$id,true);
	return $answer[0]['max_anio'];
}

function minAnioProyecto ($id) {
	$answer=$GLOBALS['db']->db_query("select min(anio) as min_anio from actividad ac join personal_horas ph on ac.id=ph.id_actividad where ac.proyecto=$id",true);
	return $answer[0]['min_anio'];
}

function maxAnioProyecto ($id) {
	$answer=$GLOBALS['db']->db_query("select max(anio) as max_anio from actividad ac join personal_horas ph on ac.id=ph.id_actividad where ac.proyecto=$id",true);
	return $answer[0]['max_anio'];
}

function sigueEnPlantillaD ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("select fecha_baja from personal p where id=".$id,true);
	$answer = reset($answer);
	if ($answer['fecha_baja']==0) return true;
	return (date("Y",$answer['fecha_baja']) >= $anio);
}

function sigueEnPlantillaI ($id,$anio) {
	$answer=$GLOBALS['db']->db_query("select fecha_alta from personal p where id=".$id,true);
	$answer = reset($answer);
	if ($answer['fecha_alta']==0) return true;
	return (date("Y",$answer['fecha_alta']) <= $anio);
}

 
/**************************************************************/
/**************************************************************/




/*CALCULO DE HORAS*********************************************/
/*CALCULO DE HORAS*********************************************/

function esFestivo ($dia,$mes) {
		if ($mes==1 && ($dia==1 || $dia==6)) return true;
		if ($mes==3 && $dia==19) return true;
		if ($mes==5 && $dia==1) return true;
		if ($mes==7 && $dia==25) return true;
		if ($mes==8 && $dia==15) return true;
		if ($mes==10 && $dia==12) return true;
		if ($mes==11 && $dia==1) return true;
		if ($mes==12 && ($dia==6 || $dia==8 || $dia==25)) return true;
		return false;
}

function laborables($f_ini_uni,$f_fin_uni) {
	$current_date=$f_ini_uni; $num=0;
	while ($current_date<=$f_fin_uni) {
		$dia=date('w',$current_date);
		if($dia>0 && $dia<6 && !esFestivo(date('j',$current_date),date('n',$current_date)))
			$num++;
		$current_date+=86400;
	}
	return $num;
}

function horasLibres ($p,$a,$m) {
	$horas_libres=8*laborables(mktime(12,0,0,$m,1,$a),mktime(12,0,0,$m+1,0,$a));
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			if($e['horas_justificadas']!=0 || $e['estado']=="JUSTIFICADO" || $e['estado']=="CONCLUIDO") $horas_libres-=$e['horas_justificadas'];
			else {
				if($e['horas_aprobadas']!=0 || $e['estado']=="APROBADO") $horas_libres-=$e['horas_aprobadas'];
				else {
					if($e['horas']!=0) $horas_libres-=$e['horas'];
				}
			}
			$e=next($existe);
		}
	}
	return $horas_libres;
}

function horasOcupadas ($p,$a,$m) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m",true);
	if(isset($existe)) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			if($e['horas_justificadas']!=0 || $e['estado']=="JUSTIFICADO" || $e['estado']=="CONCLUIDO") $horas_ocupadas+=$e['horas_justificadas'];
			else if($e['horas_aprobadas']!=0 || $e['estado']=="APROBADO") $horas_ocupadas+=$e['horas_aprobadas'];
			else if($e['horas']!=0) $horas_ocupadas+=$e['horas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}

function horasOcupadasProyecto ($p,$a,$m,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m and pr.id=$pro",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			if($e['horas_justificadas']!=0 || $e['estado']=="JUSTIFICADO" || $e['estado']=="CONCLUIDO") $horas_ocupadas+=$e['horas_justificadas'];
			else if($e['horas_aprobadas']!=0 || $e['estado']=="APROBADO") $horas_ocupadas+=$e['horas_aprobadas'];
			else if($e['horas']!=0) $horas_ocupadas+=$e['horas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}

function horasOcupadasProyectoAnio ($p,$a,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,sum(ph.horas) as horas,sum(ph.horas_aprobadas) as horas_aprobadas,sum(ph.horas_justificadas) as horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and pr.id=$pro group by ph.anio",true);
	if(isset($existe)) {
		$e=reset($existe);
		if($e['horas_justificadas']!=0 || $e['estado']=="JUSTIFICADO" || $e['estado']=="CONCLUIDO") $horas_ocupadas+=$e['horas_justificadas'];
		else if($e['horas_aprobadas']!=0 || $e['estado']=="APROBADO") $horas_ocupadas+=$e['horas_aprobadas'];
		else if($e['horas']!=0) $horas_ocupadas+=$e['horas'];
	}
	return $horas_ocupadas;
}

function horasPresentadas ($p,$a,$m) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			$horas_ocupadas+=$e['horas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}

function horasPresentadasProyectoAnio ($p,$a,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,sum(ph.horas) as horas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and pr.id=$pro group by ph.anio",true);
	if($existe) {
		$e=reset($existe);
		$horas_ocupadas+=$e['horas'];
	}
	return $horas_ocupadas;
}

function horasPresentadasProyecto ($p,$a,$m,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m and pr.id=$pro",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			$horas_ocupadas+=$e['horas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}

function horasJustificadas ($p,$a,$m) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			$horas_ocupadas+=$e['horas_justificadas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}


function horasJustificadasAnio ($p,$a) {//**************************
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,sum(ph.horas) as horas,sum(ph.horas_aprobadas) as horas_aprobadas,sum(ph.horas_justificadas) as horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a group by ph.anio",true);
	if($existe) {
		$e=reset($existe);
		if($e['horas_justificadas']!=0 || $e['estado']=="JUSTIFICADO" || $e['estado']=="CONCLUIDO") $horas_ocupadas+=$e['horas_justificadas'];
		else if($e['horas_aprobadas']!=0 || $e['estado']=="APROBADO") $horas_ocupadas+=$e['horas_aprobadas'];
		else if($e['horas']!=0) $horas_ocupadas+=$e['horas'];
	}
	return $horas_ocupadas;
}

function horasJustificadasProyectoAnio ($p,$a,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,sum(ph.horas_justificadas) as horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and pr.id=$pro group by ph.anio",true);
	if($existe) {
		$e=reset($existe);
		$horas_ocupadas+=$e['horas_justificadas'];
	}
	return $horas_ocupadas;
}

function horasJustificadasProyecto ($p,$a,$m,$pro) {
	$horas_ocupadas=0;
	$existe=$GLOBALS['db']->db_query("select pr.estado,ph.horas,ph.horas_aprobadas,ph.horas_justificadas from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.id_personal=$p and ph.anio=$a and ph.mes=$m and pr.id=$pro",true);
	if($existe) {
		$e=reset($existe);
		$num=count($existe);
		for ($i=0;$i<$num;$i++) {
			$horas_ocupadas+=$e['horas_justificadas'];
			$e=next($existe);
		}
	}
	return $horas_ocupadas;
}

function horasAnioProyecto ($a,$p,$tipo) {
	$horas_ocupadas=$GLOBALS['db']->db_query("select sum(ph.horas) as hp, sum(ph.horas_aprobadas) as ha, sum(ph.horas_justificadas) as hj from personal_horas ph join actividad ac on ph.id_actividad=ac.id join proyectos pr on ac.proyecto=pr.id WHERE ph.anio=".$a." and pr.id=".$p,true);
	if($tipo=='1') {$horas_ocupadas = $horas_ocupadas[0]['hp'];}
	elseif($tipo=='2') {$horas_ocupadas = $horas_ocupadas[0]['ha'];}
	elseif($tipo=='3') {$horas_ocupadas = $horas_ocupadas[0]['hj'];}
	return $horas_ocupadas;
}

function horasActividad ($a,$tipo) {
	$horas_ocupadas=$GLOBALS['db']->db_query("select sum(ph.horas) as hp, sum(ph.horas_aprobadas) as ha, sum(ph.horas_justificadas) as hj from personal_horas ph join actividad ac on ph.id_actividad=ac.id WHERE ac.id=".$a,true);
	if($tipo=='1') {$horas_ocupadas = $horas_ocupadas[0]['hp'];}
	elseif($tipo=='2') {$horas_ocupadas = $horas_ocupadas[0]['ha'];}
	elseif($tipo=='3') {$horas_ocupadas = $horas_ocupadas[0]['hj'];}
	return $horas_ocupadas;
}

function horasEmpActMes ($e,$ac,$a,$m,$tipo) {
	$total=0;
	$horas_ocupadas=$GLOBALS['db']->db_query("select horas as hp, horas_aprobadas as ha, horas_justificadas as hj
											  from personal_horas
											  where mes=$m and anio=$a and id_personal=$e and id_actividad=$ac",true);
	if($tipo=='1') {$total += $horas_ocupadas[0]['hp'];}
	elseif($tipo=='2') {$total += $horas_ocupadas[0]['ha'];}
	elseif($tipo=='3') {$total += $horas_ocupadas[0]['hj'];}
	return $total;
}

function horasEmpAct ($e,$ac,$tipo) {
	$total=0;
	$horas_ocupadas=$GLOBALS['db']->db_query("select sum(horas) as hp, sum(horas_aprobadas) as ha, sum(horas_justificadas) as hj
											  from personal_horas
											  where id_personal=$e and id_actividad=$ac
											  group by id_personal",true);
	if($tipo=='1') {$total += $horas_ocupadas[0]['hp'];}
	elseif($tipo=='2') {$total += $horas_ocupadas[0]['ha'];}
	elseif($tipo=='3') {$total += $horas_ocupadas[0]['hj'];}
	return $total;
}

function horasEmpMes ($id,$e,$a,$m,$tipo) {
	$total=0;
	$horas_ocupadas=$GLOBALS['db']->db_query("select sum(horas) as hp, sum(horas_aprobadas) as ha, sum(horas_justificadas) as hj
											  from personal_horas ph join actividad ac on ph.id_actividad=ac.id
											  where mes=$m and anio=$a and id_personal=$e and ac.proyecto=$id
											  group by id_personal",true);
	if($tipo=='1') {$total += $horas_ocupadas[0]['hp'];}
	elseif($tipo=='2') {$total += $horas_ocupadas[0]['ha'];}
	elseif($tipo=='3') {$total += $horas_ocupadas[0]['hj'];}
	return $total;
}

/*******************************************************/
/*******************************************************/




/*CLIENTES/PROYECTOS************************************/
/*CLIENTES/PROYECTOS************************************/
function getNomCli ($id) {
	$nomCli=$GLOBALS['db']->db_query("select nombre from clientes where id=".$id,true);
	if($nomCli) {
		return $nomCli[0]['nombre'];
	} else
		return false;
}

function getAcrCli ($id) {
	$acrCli=$GLOBALS['db']->db_query("select acronimo from clientes where id=".$id,true);
	if($acrCli) {
		return $acrCli[0]['acronimo'];
	} else
		return false;
}

function getNomPro ($id) {
	$nomPro=$GLOBALS['db']->db_query("select proyecto as nombre from proyectos where id=".$id,true);
	if($nomPro) {
		return $nomPro[0]['nombre'];
	} else
		return false;
}

function getEstPro ($id) {
	$estPro=$GLOBALS['db']->db_query("select estado as nombre from proyectos where id=".$id,true);
	if($estPro) {
		return $estPro[0]['nombre'];
	} else
		return false;
}

function getAcrPro ($id) {
	$acrPro=$GLOBALS['db']->db_query("select acronimo from proyectos where id=".$id,true);
	if($acrPro) {
		return $acrPro[0]['acronimo'];
	} else
		return false;
}

function getCliFromPro ($id) {
	$acrCli=$GLOBALS['db']->db_query("select cl.acronimo from clientes cl join proyectos pr on cl.id=pr.cliente where pr.id=".$id,true);
	if($acrCli) {
		return $acrCli[0]['acronimo'];
	} else
		return false;
}


function numProjects ($id) {//DE UN CLIENTE
	$answer1=$GLOBALS['db']->db_query("select count(*) as number from proyectos p where p.cliente=".$id,true);
	$answer2=$GLOBALS['db']->db_query("select count(*) as number from cooperacion c where c.cliente=".$id,true);
	return ($answer1[0]['number']+$answer2[0]['number']);
}

function numEmployees ($id) {
	$answer=$GLOBALS['db']->db_query("select count(*) as number from personal p  where p.id_cliente=".$id,true);
	return $answer[0]['number'];
}

function getIdNomEmployees ($id,$num) {
	$answer=$GLOBALS['db']->db_query("select p.nombre as nomEmp,p.id as idEmp from clientes c join personal p on p.id_cliente=c.id where c.id=".$id." order by p.nombre limit ".$num.",1",true);
	$answer = reset($answer);
	$res[]=$answer['idEmp'];
	$res[]=$answer['nomEmp'];
	return $res;
}

function numEmployeesProyecto ($id) {
	$answer=$GLOBALS['db']->db_query("select count(distinct(p.id)) as number
									  from personal p join personal_horas ph on p.id=ph.id_personal join actividad ac on ph.id_actividad=ac.id 
									  where ac.proyecto=$id",true);
	return $answer[0]['number'];
}

function getIdNomProjects ($id) {
	$answer=$GLOBALS['db']->db_query("select id,proyecto from proyectos where cliente=".$id." order by id",true);
	return $answer;
}

function colorEstadoProyecto ($estado) {
	switch($estado) {
		case "SIN EMPEZAR":
			return "#87CEFA";	
			break;
		case "EN CURSO":
			return "#87CEFA";	
			break;
		case "PRESENTADO":
			return "#87CEFA";
			break;
		case "APROBADO":
			return "#98FB98";
			break;
		case "JUSTIFICADO":
			return "#F4A460";
			break;
		case "CONCLUIDO":
			return "#F4A460";
			break;
		case "DENEGADO":
			return "#F27B76";
			break;
		default:
			return "#FFF";
	}
}

function colorEstadoProyecto2 ($estado) {
	switch($estado) {
		case "SIN EMPEZAR":
			return "#BDE2F8";	
			break;
		case "EN CURSO":
			return "#BDE2F8";	
			break;
		case "PRESENTADO":
			return "#BDE2F8";
			break;
		case "APROBADO":
			return "#C2F9C2";
			break;
		case "JUSTIFICADO":
			return "#FAD0AD";
			break;
		case "CONCLUIDO":
			return "#FAD0AD";
			break;
		case "DENEGADO":
			return "#F3C1C0";
			break;
		default:
			return "#FFF";
	}
}

/****************************************************************/
/****************************************************************/




/****TAREAS******************************************************/
/****************************************************************/
function proTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select proyecto from tareas where id=".$id,true);
	return $answer[0]['proyecto'];
}

function asuTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select asunto from tareas where id=".$id,true);
	return $answer[0]['asunto'];
}

function getPropietarioTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select propietario from tareas where id=".$id,true);
	return $answer[0]['propietario'];
}

function getMesVencTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select vencimiento from tareas where id=".$id,true);
	return date('n',$answer[0]['vencimiento']);
}

function getAnioVencTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select vencimiento from tareas where id=".$id,true);
	return date('Y',$answer[0]['vencimiento']);
}

function getMesPlanTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select dia from planificacion where id=".$id,true);
	return $answer[0]['dia'];
}

function getCreadorTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select creador from tareas where id=".$id,true);
	return $answer[0]['creador'];
}

function getAsuntoTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select asunto from tareas where id=".$id,true);
	return $answer[0]['asunto'];
}

function getProyectoTarea ($id) {
	$answer=$GLOBALS['db']->db_query("select proyecto from tareas where id=".$id,true);
	return $answer[0]['proyecto'];
}

function getHorasAsignadasATarea ($id) {
	$answer=$GLOBALS['db']->db_query("select sum(horas) as horas from partes where id_tarea=".$id,true);
	return $answer[0]['horas'];
}

function tareaPospuesta ($id,$dia) {
	$answer=$GLOBALS['db']->db_query("SELECT id FROM planificacion WHERE id_tarea='$id' AND dia>$dia",true);
	if (count($answer) > 0)	return true;
	else return false;
}

function tareaPospuesta2 ($id,$dia) {
	$answer=$GLOBALS['db']->db_query("SELECT id FROM planificacion WHERE id_tarea='$id' AND dia<$dia",true);
	if (count($answer) > 0)	return true;
	else return false;
}

function tareaPospuesta3 ($id,$dia) {
	if (tareaPospuesta($id,$dia+86399) && tareaPospuesta2($id,$dia)) return true;
	else return false;
}

function getPartesDia ($responsable,$dia) {
	$horas=0.0;
	$answer=$GLOBALS['db']->db_query("select sum(horas) as horas from partes where responsable='$responsable' and dia>=$dia and dia<$dia+86400 group by responsable,dia",true);
	return $horas+$answer[0]['horas'];
}

function getPartesDiaTarea ($responsable,$dia,$tarea) {
	$horas=0.0;
	$answer=$GLOBALS['db']->db_query("select sum(horas) as horas from partes where responsable='$responsable' and dia>=$dia and dia<$dia+86400 and id_tarea=$tarea group by responsable,dia",true);
	return $horas+$answer[0]['horas'];
}

function numTareasDeAaBentreFechas($creador,$propietario,$f1,$f2,$estado){
	if($estado==0) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva=''";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==1) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='Activa' and (aceptada=1 OR aceptada is null) and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==2) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='A la espera' and aceptada=1 and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==3) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='Terminada' and aceptada=1 and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==4) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva='' and borrada=1";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==5) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and creador='".$creador."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva='' and aceptada=0 and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
}
function numTareasDeBentreFechas($propietario,$f1,$f2,$estado){
	if($estado==0) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva=''";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==1) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='Activa' and (aceptada=1 OR aceptada is null) and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==2) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='A la espera' and aceptada=1 and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==3) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and estado='Terminada' and aceptada=1 and repetitiva='' and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==4) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva='' and borrada=1";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
	elseif($estado==5) {
		$query = "select count(id) as num from tareas where propietario='".$propietario."' and fecha_creacion>=$f1 and fecha_creacion<$f2 and repetitiva='' and aceptada=0 and borrada=0";
		//echo "<br><br>".$query."<br>";
		$answer = $GLOBALS['db']->db_query($query,true);
		return $answer[0]['num'];
	}
}


function horas_hasta_planificacion ($usuario,$f1,$f2) {
	$query = "select avg(pl.fecha_alta-ta.fecha_creacion) as segundos from tareas ta join planificacion pl on ta.id=pl.id_tarea where propietario='".$usuario."' and repetitiva<1 and fecha_creacion>=$f1 and fecha_creacion<$f2";
	$tiempo = $GLOBALS['db']->db_query($query,true);
	//echo "<br>".$query."<br>";
	return $tiempo[0]['segundos'];
}

function horas_hasta_finalizacion ($usuario,$f1,$f2) {
	$query = "select avg(ta.fecha_modificacion-pl.fecha_alta) as segundos from tareas ta join planificacion pl on ta.id=pl.id_tarea where propietario='".$usuario."' and repetitiva<1 and estado='Terminada' and ta.fecha_modificacion>pl.fecha_alta and fecha_creacion>=$f1 and fecha_creacion<$f2";
	$tiempo = $GLOBALS['db']->db_query($query,true);
	//echo "<br>".$query."<br>";
	return $tiempo[0]['segundos'];
}

function duracion_estimada ($usuario,$f1,$f2) {
//select duracion_estimada as estimacion, sum(horas) as partes, duracion_estimada-sum(horas) as desviacion
//from tareas ta join partes pa on ta.id=pa.id_tarea 
//where propietario='Javier' and repetitiva<1 and estado='Terminada'
//group by pa.id_tarea
	$query = "select duracion_estimada as estimacion, sum(horas) as partes from tareas ta join partes pa on ta.id=pa.id_tarea where propietario='".$usuario."' and repetitiva<1 and estado='Terminada' and fecha_creacion>=$f1 and fecha_creacion<$f2 group by pa.id_tarea";
	$tiempo = $GLOBALS['db']->db_query($query,true);
	//echo "<br>".$query."<br>";
	return $tiempo;
}

function horasDecToHumano ($tiempo) {
	if ($tiempo>0) {
		if ($tiempo<1) {
			return round($tiempo*60)."m";
		} else {
			$horas = floor($tiempo)."h";
			$minutos = round(($tiempo-floor($tiempo))*60)."";
			if ($minutos>0) return $horas.$minutos;
			else return $horas;
		}
	} else return "0";
}

/****************************************************************/
/****************************************************************/

function getUsuariosActivos () {
	$answer=$GLOBALS['db']->db_query("SELECT id,nombre,apellidos FROM usuarios WHERE listar=1 and externo<>'1' order by nombre",true);
	return $answer;
}

function getNomUsu ($id) {
	$nomUsu=$GLOBALS['db']->db_query("select nombre from usuarios where id=".$id,true);
	if($nomUsu) {
		return $nomUsu[0]['nombre'];
	} else
		return false;
}

function si_no ($valor) {
	if ($valor==1) return "Sí";
	return "No";
}
  
function copydirr($fromDir,$toDir,$chmod=0757,$verbose=false)
/*
    copies everything from directory $fromDir to directory $toDir
    and sets up files mode $chmod
*/
{
//* Check for some errors
$errors=array();
$messages=array();
if (!is_writable($toDir))
    $errors[]='target '.$toDir.' is not writable';
if (!is_dir($toDir))
    $errors[]='target '.$toDir.' is not a directory';
if (!is_dir($fromDir))
    $errors[]='source '.$fromDir.' is not a directory';
if (!empty($errors))
    {
    if ($verbose)
        foreach($errors as $err)
            echo '<strong>Error</strong>: '.$err.'<br />';
    return false;
    }
//*/
$exceptions=array('.','..');
//* Processing
$handle=opendir($fromDir);
while (false!==($item=readdir($handle)))
    if (!in_array($item,$exceptions))
        {
        //* cleanup for trailing slashes in directories destinations
        $from=str_replace('//','/',$fromDir.'/'.$item);
        $to=str_replace('//','/',$toDir.'/'.$item);
        //*/
        if (is_file($from))
            {
            if (@copy($from,$to))
                {
                chmod($to,$chmod);
                touch($to,filemtime($from)); // to track last modified time
                $messages[]='File copied from '.$from.' to '.$to;
                }
            else
                $errors[]='cannot copy file from '.$from.' to '.$to;
            }
        if (is_dir($from))
            {
            if (@mkdir($to))
                {
                chmod($to,$chmod);
                $messages[]='Directory created: '.$to;
                }
            else
                $errors[]='cannot create directory '.$to;
            copydirr($from,$to,$chmod,$verbose);
            }
        }
closedir($handle);
//*/
//* Output
if ($verbose)
    {
    foreach($errors as $err)
        echo '<strong>Error</strong>: '.$err.'<br />';
    foreach($messages as $msg)
        echo $msg.'<br />';
    }
//*/
return true;
}

function borrardir($carpeta)
{
		if (is_dir($carpeta)){
		  $directorio = opendir($carpeta);
		  while ($archivo = readdir($directorio)){
			if( $archivo !='.' && $archivo !='..' ){
				//si es un directorio, volvemos a llamar a la función para que elimine el contenido del mismo
			  if ( is_dir( $carpeta."/".$archivo ) ){
			    borrardir( $carpeta."/".$archivo );
			  } else {
				//si no es un directorio, lo borramos
				unlink($carpeta."/".$archivo);
			  }
			}
		  }
		  closedir($directorio);
		  rmdir($carpeta);
		}
}
?>
