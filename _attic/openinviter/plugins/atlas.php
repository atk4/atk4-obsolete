<?php
$_pluginInfo=array(
	'name'=>'Atlas',
	'version'=>'1.0.0',
	'description'=>"Get the contacts from a Atlas account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.atlas.cz/'
	);
/**
 * Atlas.cz Plugin
 * 
 * Imports user's contacts from Atlas.cz AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class atlas extends OpenInviter_Base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	public $requirement='user';
	public $allowed_domains=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'name',
				'login_post'=>'jAjaxValidatorManager',
				'url_contacts'=>'rm'
				);
	
	/**
	 * Login function
	 * 
	 * Makes all the necessary requests to authenticate
	 * the current user to the server.
	 * 
	 * @param string $user The current user.
	 * @param string $pass The password for the current user.
	 * @return bool TRUE if the current user was authenticated successfully, FALSE otherwise.
	 */
	public function login($user,$pass)
		{
		$this->resetDebugger();
		$this->service='atlas';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://profil.centrum.cz/login.aspx");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://profil.centrum.cz/login.aspx",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get','http://profil.centrum.cz/login.aspx','GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action='http://profil.centrum.cz/verify.aspx';
		$post_elements=array('name'=>$user,'password'=>$pass);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_contacts='http://amail.centrum.cz/addressbook.aspx';
		$this->login_ok=$url_contacts;
		return true;
		}

	/**
	 * Get the current user's contacts
	 * 
	 * Makes all the necesarry requests to import
	 * the current user's contacts
	 * 
	 * @return mixed The array if contacts if importing was successful, FALSE otherwise.
	 */	
	public function getMyContacts()
		{
		if (!$this->login_ok)
			{
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		else $url=$this->login_ok;
		$res=$this->get($url,true);
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('contacts_file',$url,'GET');
		else
			{
			$this->updateDebugBuffer('contacts_file',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts = array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//span[@class='rm']";$data=$xpath->query($query);$name="";
		foreach($data as $node) 
			{
			$stringBulk=$node->nodeValue;
			if (strpos($stringBulk,'@')!==false) {$email=$stringBulk;$name="";}else $name.=$stringBulk;
			if (!empty($email)) $contacts[$email]=(isset($name)?$name:$email);
			}
		foreach ($contacts as $email=>$name) if (!$this->isEmail($email)) unset($contacts[$email]);
		return $contacts;	
		}

	/**
	 * Terminate session
	 * 
	 * Terminates the current user's session,
	 * debugs the request and reset's the internal 
	 * debudder.
	 * 
	 * @return bool TRUE if the session was terminated successfully, FALSE otherwise.
	 */	
	public function logout()
		{
		if (!$this->checkSession()) return false;		
		$res=$this->get('http://www.atlas.cz/r/?ump',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>