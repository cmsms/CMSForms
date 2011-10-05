<?php

	/*
		This class aim to handle CMS Forms very differently.
		
		Author: Jean-Christophe Cuvelier <cybertotophe@gmail.com>
		Copyrights: Jean-Christophe Cuvelier - Morris & Chapman Belgium - 2010
		Licence: GPL		
	
	*/

class CMSForm	//extends	CmsObject
{
	
	protected $module_name;
	protected $id; // The form ID
	protected $returnid;
	protected $action;	
	protected $method = 'get';
	protected $enctype;
	
	protected $widgets = array(); // The form widgets
	protected $hidden_widgets = array(); // Specific widgets shown on the begin;
	protected $fieldsets = array(); // Sub forms for fieldsets
	
	protected $labels = array(
		'submit' => 'Submit',
		'apply' => 'Apply',
		'cancel' => 'Cancel',
		'next' => 'Next',
		'previous' => 'Previous'
		);
		
	protected $active_buttons = array('submit','cancel');
	protected $form_errors = array(); 
	protected $show_priority = false;
	
	protected $templates = array(
		'widget' => '<div class="pageoverflow">
			<div class="pagetext">%LABEL%:</div>
			<div class="pageinput">%INPUT% <em>%TIPS%</em></div>
			<div class="pageinput" style="color: red;">%ERRORS%</div>
		</div>',
		'errors' => '<div style="color: red;">%ERRORS%</div>',
		'buttons' => '<p>%BUTTONS%</p>'
		);
	
	public function __construct($module_name, $id, $action, $returnid)
	{
		$this->module_name = $module_name;
		$this->id = $id;
		$this->returnid = $returnid;
		$this->action = $action;
	}
	
	public function setMethod($method = 'get')
	{
		if ($method == 'post')
		{
			$this->method = 'post';
		}
	}
	
	public function setMultipartForm()
	{
		$this->method = 'post';
		$this->enctype = 'multipart/form-data';
	}
	
	public function process()
	{
		foreach($this->widgets as $widget)
		{
			$widget->process();
		}
		
		foreach($this->hidden_widgets as $widget)
		{
			$widget->process();
		}		
		
		foreach($this->fieldsets as $fieldset)
		{
			$fieldset->process();
		}
	}
	
	public function render()
	{	
		// TODO: LOAD TEMPLATE FROM FILE...
		$template = file_get_contents(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin.form.tpl');
		
		$smarty = new Smarty();
		$smarty->assign('form', $this);		
		$smarty->_compile_source('temporary template', $template, $_compiled );
		@ob_start();
		$smarty->_eval('?>' . $_compiled);
		$_contents = @ob_get_contents();
		@ob_end_clean();
		return $_contents;
	}
	
	public function setTemplate($template_name, $template)
	{
		$this->templates[$template_name] = $template;
	}
	
	public function getHeaders()
	{
		if (cms_utils::get_module($this->module_name))
		{			
			$html = cms_utils::get_module($this->module_name)->CreateFormStart($this->id, $this->action, $this->returnid, $this->method, $this->enctype);
			foreach($this->hidden_widgets as $widget)
			{
				$html .= $widget->getInput();
			}			
			return $html;
		}
		return null;
	}
	
	public function getFooters()
	{
		if ((cms_utils::get_module($this->module_name)))
		{			
			$html = cms_utils::get_module($this->module_name)->CreateFormEnd();
			return $html;
		}
		return null;
	}
	
	public function setLabel($label, $title)
	{
		$this->labels[$label] = $title;
	}
	
	public function setLabels($labels = array())
	{
		foreach($labels as $label => $title)
		{
			$this->setLabel($label, $title);
		}
	}	
	
	public function setButtons($buttons = array())
	{
		if (is_array($buttons))	$this->active_buttons = $buttons;
	}
	
	public function getButtons()
	{
		$html = '';
		foreach($this->active_buttons as $button)
		{
			// $html .= cms_utils::get_module($this->module_name)->CreateInputSubmit($this->id, $button, $this->labels[$button]);
			$html .=  cms_utils::get_module($this->module_name)->CreateInputSubmit($this->id, $button, $this->labels[$button]);
		}
		
		return $html;
	}
	
	public function setWidget($name,$type,$settings = array())
	{
		$widget = new CMSFormWidget($this,$this->id,$this->module_name,$name,$type,$settings);
		if ($type == 'hidden')
		{
			$this->hidden_widgets[$name] = $widget;
		}
		elseif ($type == 'file')
		{
			$this->setMultipartForm(); // If we add files, we have to be posted multipart forms...
			$this->widgets[$name] = $widget;
		}		
		elseif ($type == 'password')
		{
			$this->setMethod('post'); // If we ask password, we should always post the form for security reason !
			$this->widgets[$name] = $widget;
		}		
		elseif (($type == 'textarea') || ($type == 'codearea'))
		{ 
			$this->setMethod('post'); // If we ask password, we should always post the form for security reason !
			$this->widgets[$name] = $widget;
		}
		else
		{
			$this->widgets[$name] = $widget;
		}
	}
	
	public function getWidgets()
	{
		return $this->widgets;
	}
	
	public function getWidget($name)
	{
		if (isset($this->widgets[$name]))
		{
			return $this->widgets[$name];
		}
		elseif (isset($this->hidden_widgets[$name]))
		{
			return $this->hidden_widgets[$name];
		}
		else
		{
			return null;
		}
	}
	
	public function hideWidget($name)
	{
		if (isset($this->widgets[$name]))
		{
			$this->widgets[$name]->hide();
		}
		elseif (isset($this->hidden_widgets[$name]))
		{
			$this->hidden_widgets[$name]->hide();
		}
	}
	
	public function removeWidget($name)
	{
		if (isset($this->widgets[$name]))
		{
			unset($this->widgets[$name]);
		}
		elseif (isset($this->hidden_widgets[$name]))
		{
			unset($this->hidden_widgets[$name]);
		}
	}
	
	
	
	public function showWidgets($template=null, $force=false)
	{
		$html = '';	
		foreach($this->widgets as $widget)
		{
			$html .= $widget->show($template, $force);
		}
		return $html;
	}
	
	public function hasErrors()
	{
		$has_errors = false;
		
		foreach($this->widgets as $widget)
		{
			if($widget->hasErrors())
			{
				$has_errors = true;
			}
		}
		
		foreach($this->hidden_widgets as $widget)
		{
			if($widget->hasErrors())
			{
				$has_errors = true;
			}
		}
		
		foreach($this->fieldsets as $fieldset)
		{
			if($fieldset->hasErrors())
			{
				$has_errors = true;
			}
		}
		
		if (count($this->form_errors) != 0)
		{
			$has_errors = true;
		}
		
		return $has_errors; 
	}
	
	public function noError()
	{
		return !$this->hasErrors();
	}
	
	public function getErrors()
	{
		return $this->form_errors;
	}
	
	public function showErrors()
	{
		$html = '';
		if(count($this->form_errors) > 0)
		{
			$html .= '<ul class="errors">';
			foreach($this->form_errors as $priority => $errors)
			{
				$html .= '<li>';
				if ($this->show_priority)	$html .= '<em class="form_error_priority">'.$priority.'</em>';
				$html .= '<ul>';
				foreach($errors as $error)
				{
					$html .= '<li>'.$error.'</li>';
				}				
				$html .= '</ul></li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}
	
	public function setError($message,$priority='default')
	{
		$this->form_errors[$priority][] = $message;
	}
	
	public function isSubmitted()
	{
		if (isset($_REQUEST[$this->id.'submit']))
		{
			return true;
		}
		return false;
	}
	
	public function isApplied()
	{
		if (isset($_REQUEST[$this->id.'apply']))
		{
			return true;
		}
		return false;
	}	
	
	public function isCancelled()
	{
		if (isset($_REQUEST[$this->id.'cancel']))
		{
			return true;
		}
		return false;
	}

	public function isPosted()
	{
		// Add check for next and previous
		if ($this->isSubmitted() || $this->isApplied())
		{
			return true;
		}
		return false;
	}
	
	public function isPushed($button)
	{
		if (isset($_REQUEST[$this->id.$button]))
		{
			return true;
		}
		return false;
	}
	
	public function isSent()
	{
			foreach($this->active_buttons as $button)
			{
					if (isset($_REQUEST[$this->id.$button]))
					{
						return true;
					}
			}
			
			return false;
	}
	
	
	
	public function setFieldset($name, $legend='')
	{
		if(!isset($this->fieldsets[$name]))
		$this->fieldsets[$name] = new CMSFormFieldset($this,$name,$legend);
	}
	
	public function getFieldset($name)
	{
		if (isset($this->fieldsets[$name]))
		{
			return $this->fieldsets[$name];
		}
		return null;
	}
	
	public function renderFieldsets($widget_template = null)
	{
		$html = '';
		foreach($this->fieldsets as $fieldset)
		{
			$html .= $fieldset->render($widget_template);
		}
		return $html;
	}
	
	public function renderFieldset($name, $widget_template = null)
	{
		if (isset($this->fieldsets[$name]))
		{
			return $this->fieldsets[$name]->render($widget_template);
		}
		return null;
	}
	
}

class CMSFormFieldset extends CMSForm
{
	protected $parent_form;
	protected $name;
	protected $legend;
	protected $class;
	protected $rendered = false;
	
	public function __construct($form, $name, $legend = '')
	{
		parent::__construct($form->module_name, $form->id, $form->action, $form->returnid);
		$this->name = $name;
		if($legend == '')
		{
			$this->legend = $name;
		}
		else
		{
			$this->legend = $legend;
		}
		$this->parent_form =& $form;
	}
	
	public function setClass($class)
	{
		$this->class = $class;
	}
	
	public function render($widget_template=null)
	{
		$html = '';
		if(!$this->rendered)
		{
			$html .= '<fieldset';
			$html .= ($this->class)?' class="' . $this->class . '"':'';
			$html .='><legend>'.$this->legend.'</legend>';
			$html .= $this->showWidgets($widget_template);
			$html .= '</fieldset>';
			$this->rendered = true;
		}
		return $html;
		
	}
	
	public function setWidget($name,$type,$settings = array())
	{
		if ($type == 'file')
		{
			$this->parent_form->setMultipartForm();
		}
		parent::setWidget($name,$type,$settings);
	}
	
}
