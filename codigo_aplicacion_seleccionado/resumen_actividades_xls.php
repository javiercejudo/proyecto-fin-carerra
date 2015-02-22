<?php
include ("include/sesion.php");

if($nivel<1)
	exit("Error: no tiene permiso para entrar aqu&iacute;.");

//error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require_once 'phpexcel/Classes/PHPExcel.php';

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Ingeniería e Innovación")
							 ->setLastModifiedBy("Javier Cejudo")
							 ->setTitle("Resumen de personal");

$id_proyecto=$_REQUEST['id_proyecto'];
$tipo=$_REQUEST['tipo'];
if($tipo==1) { $nombre_tipo="presentadas";}
elseif($tipo==2) { $nombre_tipo="aprobadas";}
elseif($tipo==3) { $nombre_tipo="justificadas";}

if($id_proyecto && getNomPro($id_proyecto)!=false) {
	$nomPro = getNomPro($id_proyecto);
	$acrPro = getAcrPro($id_proyecto);
	$numEmp = numEmployeesProyecto($id_proyecto);
	$numAct = numActividadesProyecto($id_proyecto);
	//-----------------------------------------------------------------------------------
	
	if (numAniosProyecto($id_proyecto)>0) {
		for($cont_emp=0; $cont_emp<$numEmp; $cont_emp++){
			$empleado = getIdNomEmployeesProyecto($id_proyecto,$cont_emp);
			
			$objWorksheet1 = $objPHPExcel->createSheet();
			$objWorksheet1->setTitle(utf8_encode(ucwords(strtolower($empleado[1]))));
			$objPHPExcel->setActiveSheetIndex($cont_emp+1);
			
			$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
			$objPHPExcel->getDefaultStyle()->getAlignment()
							->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(4);

			$objPHPExcel->getActiveSheet()->mergeCells('B1:Z1');
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(14);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
			
			$objPHPExcel->getActiveSheet()->getStyle('A')
				->getAlignment()->setWrapText(false);
			

			$objPHPExcel->getActiveSheet()->getPageSetup()
							->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			$objPHPExcel->getActiveSheet()->getPageSetup()
							->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);			
			
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()
							->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			
			$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()
							->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$objPHPExcel->getActiveSheet()->getStyle('A')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('4')->getAlignment()
							->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('4')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('3')->getFont()->setBold(true);
						
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->freezePane('B5');
			
			$objPHPExcel->getActiveSheet()
	            ->setCellValue('B1', utf8_encode($acrPro)." (horas $nombre_tipo de ".utf8_encode(ucwords(strtolower($empleado[1]))).")");
			
			if ($numAct>0){
				$min_anio = minAnioProyecto($id_proyecto);
				$max_anio = maxAnioProyecto($id_proyecto);
				$k=$min_anio;
				$contador_columnas=1;
				while($k<=$max_anio){
					$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($contador_columnas,3,$contador_columnas+11,3);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, 3, $k);
					if($k%2==0) {$color='EEEEEE';}
					else {$color='CCCCCC';}
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, 3)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB($color);
					for ($i=0;$i<12;$i++) {
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, 4, $meses_cortos[$i+1]);
						$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, 4)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB($color);					
						$contador_columnas++;
					}
					$k++;
				}
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, 4, 'Total');
				$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($contador_columnas)->setWidth(7);
				
				for($i=0;$i<$numAct;$i++){
					$actividad = getIdNomActividadesProyecto($id_proyecto,$i);
					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i+5, utf8_encode($actividad[1]));
					
					$k=$min_anio;
					$contador_columnas=1;
					while($k<=$max_anio){
						for ($j=0;$j<12;$j++) {
							$horas_totales = horasEmpActMes($empleado[0],$actividad[0],$k,$j+1,$tipo);
							if ($horas_totales==0) $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, '');	
							else $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, $horas_totales);
							$contador_columnas++;
						}
						$k++;
					}
					$coord_min = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$i+5)->getCoordinate();
					$coord_max = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($contador_columnas-1,$i+5)->getCoordinate();
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, '=SUM('.$coord_min.':'.$coord_max.')');
					//alternativa (no borrar): $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, horasEmpAct($empleado[0],$actividad[0],$tipo));
					
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $i+5)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB('EEEEEE');
				}
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numAct+5, 'Total');
				$contador_columnas=1;
				$k=$min_anio;
				$total_global=0;
				while($k<=$max_anio){
					if($k%2==0) {$color='EEEEEE';}
					else {$color='CCCCCC';}
					for ($i=0;$i<12;$i++) {
						$coord_min = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($contador_columnas,5)->getCoordinate();
						$coord_max = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($contador_columnas,$numAct+4)->getCoordinate();
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $numAct+5, '=SUM('.$coord_min.':'.$coord_max.')');
						//alternativa (no borrar): $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $numAct+5, horasEmpMes($id_proyecto,$empleado[0],$k,$i+1,$tipo));					
						
						$total_global += horasEmpMes($id_proyecto,$empleado[0],$k,$i+1,$tipo);
						$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $numAct+5)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB($color);
						$contador_columnas++;
					}
					$k++;
				}
				$coord_min = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($contador_columnas,5)->getCoordinate();
				$coord_max = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($contador_columnas,$numAct+4)->getCoordinate();
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $numAct+5, '=SUM('.$coord_min.':'.$coord_max.')');
				//alternativa (no borrar): $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $numAct+5, $total_global);
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $numAct+5)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB('222222');
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $numAct+5)
										->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			}
		}
	}
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
$objPHPExcel->setActiveSheetIndex(1);

// Redirect output to a clients web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
$nombre = "resumen_personal_".$acrCli.".xls";
header('Content-Disposition: attachment;filename="resumen_actividades.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
