<?php
/*
 * Created on 17.09.2007 by *Camper* (camper@adevel.com)
 */
class CmdForm extends Form{
	protected $section_opened=false; 	// shows whether section is open
	protected $section_count=0;			// counts sections on form
	public $section_open=null;			// template chunk that opens new section
	public $section_close=null;			// template chunk that closes section

	function init(){
		// template->get
		$this->section_open=$this->template->is_set('section_open')?$this->template->cloneRegion('section_open'):
		$this->template->cloneRegion('form_comment');
		$this->section_close=$this->template->is_set('section_close')?$this->template->cloneRegion('section_close'):
		$this->template->cloneRegion('form_comment'); 	// yes, this cloning should be twice in the order like it is
														// if you comment out one of them, you'll see
		$this->template->tryDel('section_open');
		$this->template->tryDel('section_close');

		parent::init();
	}

	protected function getChunks(){
		// commonly replaceable chunks
		$this->grabTemplateChunk('form_comment');
		$this->grabTemplateChunk('form_separator');
		$this->grabTemplateChunk('form_line');      // template for form line, must contain field_caption,field_input,field_error
		if($this->template->is_set('hidden_form_line'))
			$this->grabTemplateChunk('hidden_form_line');
		$this->grabTemplateChunk('field_error');    // template for error code, must contain field_error_str
		//$this->grabTemplateChunk('form');           // template for whole form, must contain form_body, form_buttons, form_action,
													//  and form_name
		$this->grabTemplateChunk('field_mandatory'); // template for marking mandatory fields

		// ok, other grabbing will be done by field themselves as you will add them to the form.
		// They will try to look into this template, and if you don't have apropriate templates
		// for them, they will use default ones.
		$this->template_chunks['form']=$this->template;
		$this->template_chunks['form']->del('form_body');
		$this->template_chunks['form']->del('form_buttons');
		$this->template_chunks['form']->set('form_name',$this->name);
		return $this;
	}

	function update(){
		foreach($this->elements as $short_name=>$element){
			if($element instanceof Form_Field &&
				($element->get()===''||is_null($element->get())))
				$this->set($short_name,$element->default_value);
		}
		return parent::update();
	}

	function addField($type,$name,$caption=null,$attr=null){
		// opening Section if there is no any
		if(!$this->section_opened)$this->newSection();

		return parent::addField($type,$name,$caption,$attr);
	}
	function addComment($comment){
		if(!$this->section_opened)$this->newSection();

		return parent::addComment($comment);
	}
	/**
	 * Starts new section
	 * $separator=true defines separator style
	 */
	function newSection($separator=false){
		/*
		 * Opens column/row
		 *
		 */
		if(!is_object($this->section_open))return $this;
		$section=clone $this->section_open;
		if($this->section_opened)$this->closeSection();
		if($separator===false)$section->tryDel('SeparatorStyle');
		$this->section_count++;
		$this->add('Text','d_'.$this->section_count.'_open','form_body')->set($section->render());
		$this->section_opened=true;
		return $this;
	}
	function closeSection(){
		/*
		 * closes column/row.
		 * Should be called automatically during render()/newSection(), so there is no need
		 * to call it in the code
		 */
		$this->add('Text','d_'.$this->section_count.'_close','form_body')->set($this->section_close->render());
		$this->section_opened=false;
		return $this;
	}
	function render(){
		// setting colspan to ection count
		$this->template->trySet('section_count',$this->section_count);
		// when closing last Section we should be careful as the closing tags should NOT after all the tags,
		// but BEFORE form buttons
		// so the following commented line won't work
		if($this->section_opened)$this->template_chunks['form']->append('form_body',$this->section_close->render());
		parent::render();
	}
	function defaultTemplate(){
		return array('mform','_top');
	}
	function getAllData(){
		$data=array();
		foreach($this->elements as $key=>$val){
			if($val instanceof Form_Field){
				if($val->no_save!==true)$data[$key]=$val->get();
			}
		}
		return $data;
	}
	function validateField($condition,$msg='',$field=null){
		if(is_null($field))$field=$this->last_field;
		if(!is_object($field))$field=$this->getElement($field);
		$field->addHook('validate','if(!('.$condition.'))$this->displayFieldError("'.
			($msg?$msg:'Error in ".$this->caption."').'");');
		return $this;
	}
	/**
	 * Returns false if $value of specified $field already exists in DB
	 */
	function validateUnicity($field,$value){
		return $this->api->db->dsql()->table($this->dq->args['table'])
			->where($field,$value)
			->field('count(*)')
			->do_getOne()==0;
	}

	/*function addComment($comment){
		// as we use multiple columns - we should reopen Section before adding comment/separator
		$this->newSection();
		parent::addComment($comment);
		$this->newSection();
		return $this;
	}
	function addSeparator($separator='<hr>'){
		// as we use multiple columns - we should reopen Section before adding comment/separator
		$this->newSection();
		parent::addSeparator($separator);
		$this->newSection();
		return $this;
	}*/
}
