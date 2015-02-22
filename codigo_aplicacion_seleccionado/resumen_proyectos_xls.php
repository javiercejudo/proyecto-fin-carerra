<?php
include ("include/sesion.php");

if($nivel<3)
	exit("Error: no tiene permiso para entrar aqu&iacute;.");

//error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require_once 'phpexcel/Classes/PHPExcel.php';

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Ingeniería e Innovación")
							 ->setLastModifiedBy("Javier Cejudo")
							 ->setTitle("Resumen de personal");

$id_cliente=$_REQUEST['id_cliente'];
$tipo=$_REQUEST['tipo'];

if($id_cliente && getNomCli($id_cliente)!=false) {
	$nomCli = getNomCli($id_cliente);
	$acrCli = getAcrCli($id_cliente);
	$numEmp = numEmployees($id_cliente);
	
	$objPHPExcel->setActiveSheetIndex(0)
	            ->setCellValue('B1', utf8_encode($nomCli));
	$objPHPExcel->getActiveSheet()->mergeCells('B1:H1');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

	
	$objPHPExcel->getActiveSheet()->getPageSetup()
					->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$objPHPExcel->getActiveSheet()->getPageSetup()
					->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	
	$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
	
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getDefaultStyle()->getAlignment()
					->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);;
	
	$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()
					->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$objPHPExcel->getActiveSheet()->getStyle('A')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('4')->getAlignment()
					->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle('4')->getFont()->setBold(true);
	            
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
	//-----------------------------------------------------------------------------------
	
	if (numProjects($id_cliente)>0) {
		if (numEmployees($id_cliente)>0){
			$min_anio = minAnio($id_cliente);
			$max_anio = maxAnio($id_cliente);
			$k=$min_anio;
			$contador_columnas=1;
			while($k<=$max_anio){
				
				//************************************
				$projects=getIdNomProjectsAnio($id_cliente,$k);
				$numPro = count($projects);
				for ($i=0;$i<$numPro;$i++) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, 4, $projects[$i]['acronimo']);
					
					$color = substr(colorEstadoProyecto2($projects[$i]['estado']),1);		
					
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas,4)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB($color);
					
					$contador_columnas++;
				}
				//************************************
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas,4)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB('EEEEEE');
				$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($contador_columnas,4)->setWidth(8);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, 4, $k);
				
				$contador_columnas++;
				$k++;
			}
			
			for($i=0;$i<$numEmp;$i++){
				$empleado = getIdNomEmployees($id_cliente,$i);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i+5, utf8_encode(ucwords(strtolower($empleado[1]))));
				
				$k=$min_anio;
				$contador_columnas=1;
				while($k<=$max_anio){
					//**********************principio12
					$projects=getIdNomProjectsAnio($id_cliente,$k);
					$numPro = count($projects);
					for ($l=0;$l<$numPro;$l++) {
						$total_ocupadas = horasOcupadasProyectoAnio($empleado[0],$k,$projects[$l]['id']);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, $total_ocupadas);
						
						if($i % 2 == 0) $color = substr(colorEstadoProyecto($projects[$l]['estado']),1);
						else $color = substr(colorEstadoProyecto2($projects[$l]['estado']),1);		
					
						$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $i+5)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB($color);
						
						$contador_columnas++;
					}
					//**********************fin12
					$total_ocupadas = 0;
					for ($j=0;$j<12;$j++) {
						$total_ocupadas += horasOcupadas($empleado[0],$k,$j+1);
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($contador_columnas, $i+5, $total_ocupadas);
					
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $i+5)->getFill()
								->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
								->getStartColor()->setRGB('EEEEEE');
					if ($total_ocupadas > horasAnioPersonal ($empleado[0],$k)) {
						$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contador_columnas, $i+5)
										->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
					}
								
					//

								
					$contador_columnas++;
					$k++;
				}
			}
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numEmp+7, 'Leyenda:');
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numEmp+8, 'Sin empezar, En curso, Presentado');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numEmp+9, 'Aprobado');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numEmp+10, 'Justificado, Concluido');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $numEmp+11, 'Denegado');
			
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $numEmp+8)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setRGB(substr(colorEstadoProyecto2('SIN EMPEZAR'),1));
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $numEmp+9)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setRGB(substr(colorEstadoProyecto2('APROBADO'),1));
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $numEmp+10)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setRGB(substr(colorEstadoProyecto2('JUSTIFICADO'),1));
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $numEmp+11)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setRGB(substr(colorEstadoProyecto2('DENEGADO'),1));
		}	
	}
}

// Freeze panels and Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->getActiveSheet()->freezePane('B5');
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clients web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
$nombre = "resumen_personal_".$acrCli.".xls";
header('Content-Disposition: attachment;filename="resumen_personal.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
