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
class PdfGenerator extends AbstractController{
	protected $output_path=null;
	protected $input_path=null;	// contains html files and pictures to convert
	protected $htmldoc_name=null;
	private $convert_method='doc'; // possible values are 'doc' (use HTMLDoc soft), 'fpdf', (use fpdf)
	private $file_name=null;
	
	function init(){
		parent::init();
		$this->setOutputPath($this->api->getConfig('reports/pdf/output'));
		$this->setInputPath($this->api->getConfig('reports/pdf/input'));
	}
	function setMethod($method){
		$this->convert_method=$method;
		return $this;
	}
	function setOutputPath($path){
		$this->output_path=$path;
		return $this;
	}
	function getOutputPath(){
		return $this->output_path;
	}
	function setInputPath($path){
		$this->input_path=$path;
		return $this;
	}
	function getInputPath(){
		return $this->input_path;
	}
	function setHtmlDocName($filename){
		$this->htmldoc_name=$filename;
		return $this;
	}
	function getHtmlDocName(){
		if(!file_exists($this->htmldoc_name))throw new BaseException("HTMLDoc not found in the specified location, " .
				"check your config file");
		return $this->htmldoc_name;
	}
	function getFileName(){
		return $this->file_name; 
	}
	function setFileName($filename){
		$this->file_name = $filename;
		return $this; 
	}
	function getInputFile(){
		return $this->getInputPath().'/'.$this->getFileName().'.html';
	}
	function getOutputFile(){
		return $this->getOutputPath().'/'.$this->getFileName().'.pdf';
	}
	/**
	 * Converts the given HTML code to PDF and puts it into predefined output path
	 * Uses predefined convert method
	 * 
	 * @param $html rendered HTML code. Could be also a plain text, play with it
	 * @return path to created PDF file
	 */
	function convertHtml($html){
		// saving provided html to the file
		$this->setFileName(substr(md5($html),0,10));
		if(file_put_contents($this->getInputPath().'/'.$this->getFileName().'.html',$html)===false)
			throw new BaseException("Cannot write to ".$this->getInputPath().'/'.$this->getFileName().".html");
		switch($this->convert_method){
			case 'doc': return $this->convertHtmlDoc($this->getFileName());
			case 'fpdf': return $this->convertHtmlFpdf($this->getFileName());
			
			default: throw new BaseException('No convert method specified');
		}
	}
	private function convertHtmlFpdf($file_name){
		define( '_VALID_MOS',true );
		require($this->api->getConfig('reports/pdf/fpdf_path').'/html2fpdf.php');
		$pdf=new HTML2FPDF();
		$pdf->AddPage();
		$fp = fopen($this->getInputPath().'/'.$file_name,"r");
		$strContent = fread($fp, filesize($this->getInputPath().'/'.$file_name));
		fclose($fp);
		$pdf->WriteHTML($strContent);
		$pdf->Output($this->getOutputPath().'/'.$file_name.'.pdf');
		return $this->getOutputPath().'/'.$file_name.'.pdf';
	}
	private function convertHtmlDoc($file_name){
		$this->setHtmlDocName($this->api->getConfig('reports/pdf/htmldoc_name'));
		// executing htmldoc on this file
		$app=$this->add('AppLauncher')->setApplication($this->getHtmlDocName())
			->setParameter($this->getInputPath().'/'.$file_name.'.html')	// source file
			->setParameter('--webpage')			// webpage mode required for html sources
			->setParameter('-d',$this->getOutputPath())	// where to put
			->setParameter('-f',$this->getOutputPath().'/'.$file_name.'.pdf')		// result name
			->setParameter('-t','pdf')					// output format
		;
		$r=$app->execute();
		if($r!==0)throw new BaseException("Error converting to PDF: \n".$app->getCommandLine()."\n".
			print_r($app->getOutput()));
		// everything is ok
		//unlink($file_name.'.html');	// deleting source file
		return $this->getOutputPath().'/'.$file_name.'.pdf';
	}
}
