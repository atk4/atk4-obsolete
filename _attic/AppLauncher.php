<?php
/**
 * Executes the console application and returns a result
 * Before execution it properly formats command line
 *
 * You can use this class as a parent for descendants with extended options,
 * e.g. add methods like setParameter($value) that will append $app_params in proper way
 *
 * Consider looking into amodules3/System/ProcessIO.php for more flexible and secure
 * interaction with executables
 */
class AppLauncher extends AbstractController{
	protected $app_name=null;
	protected $app_params=array();
	protected $output=null;
	protected $command_line='';
	protected $passthru=false;	// indicates whether to use passthru command instead of exec
	protected $use_shell=false;

	function setApplication($app_name,$app_params=array()){
		$this->app_name=$app_name;
		foreach($app_params as $param=>$value){
			if(is_numeric($param)){
				// adding splitted value
				list($p,$v)=split(' ',$value);
				$this->setParameter($p,$v);
			}else{
				$this->setPatameter($param,$value);
			}
		}
		return $this;
	}
	function setParameter($parameter,$value=null){
		$this->app_params[$parameter]=$value;
		return $this;
	}
	function usePassthru($value=true){
		$this->passthru=$value;
		return $this;
	}
	function useShell($value=true){
		$this->use_shell=$value;
		return $this;
	}
	function execute(){
		$return_var=0;
		$this->command_line=$this->app_name;
		if(!empty($this->app_params)){
			foreach($this->app_params as $param=>$value){
				$this->command_line.=" $param $value";
			}
			// making errors redirected
			//$this->command_line.=' 2>&1';
		}
		if($this->use_shell){
			$result=shell_exec($this->command_line);
			$this->output=split("\n",$result);
		}
		elseif($this->passthru){
			passthru($this->command_line,$return_var);
		}
		else{
			$result=exec($this->command_line,$this->output,$return_var);
			//$this->output[]=$result;
		}
		return $return_var;
	}
	function getOutput(){
		return is_array($this->output)?$this->output:array();
	}

	function getCommandLine(){
		// returns the last command line executed
		return $this->command_line;
	}
}
