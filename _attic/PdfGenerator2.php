<?php
/**
 * Generates PDF file from the given source.
 * Sources could be:
 * - rendered HTML
 * - saved HTML file with pictures
 * 
 * Read HTMLDoc documentation forrestrictions and conditions
 * 
 * Created on 06.03.2008 by *Camper* (camper@adevel.com)
 */

class PdfGenerator2 extends System_ProcessIO {
	private $convertor='/usr/local/bin/prince';
	private $arguments=array('-','--input=html','--output=-');
	private $output_filename='preview.pdf';
	
	
	function convertHtml($html){
		$this
			->exec($this->convertor,$this->arguments)
			->write_all($html)
			;
//		$err=$this->read_all('err');
		$pdf=$this->read_all();
		$this->terminate();
		if(trim($err)){
			echo $html;
			echo "<pre>".$err;
		}else{
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="'.$this->output_filename.'"');
			echo $pdf;
		}
		exit;
	}
}
