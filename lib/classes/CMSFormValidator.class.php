<?php

	/*
		This class aim to handle CMS Forms very differently.
		
		Author: Jean-Christophe Cuvelier <cybertotophe@gmail.com>
		Copyrights: Jean-Christophe Cuvelier - Morris & Chapman Belgium - 2010
		Licence: GPL		
	
	*/
	
class CMSFormValidator
{
	protected $widget;
	protected $validator;
	protected $params;
	
	public function __construct(&$widget,$validator,$params)
	{
		$this->widget = $widget;
		$this->validator = $validator;
		$this->params = $params;
	}
	
	public function check()
	{
		switch($this->validator)
		{
			case 'not_empty':
				return $this->CheckNotEmpty();
			case 'equal_field':
				return $this->CheckEqualField();
			case 'email':
				return $this->CheckEmail();
			case 'unique':
				return $this->CheckUnique();
			default:
				throw new Exception('Unknown form validator "'.$this->validator .'".');
		}
		return true;
	}
	
	protected function CheckUnique()
	{
		$value = $this->widget->getValue();
		if (call_user_func($this->params, $value) !== null)
		{
			global $gCms;
			throw new Exception($gCms->modules['CMSForms']['object']->lang('field not unique', $value));
		}
		return true;
	}
	
	protected function CheckNotEmpty()
	{	
		global $gCms;
		$values = $this->widget->getValues();
		$value = $this->widget->getValue();
		if (is_array($values) && count($values) == 0)
		{
			throw new Exception($gCms->modules['CMSForms']['object']->lang('field_cannot_be_empty', $this->widget->getFriendlyName()));
		}
		elseif(empty($values))
		{
			throw new Exception($gCms->modules['CMSForms']['object']->lang('field_cannot_be_empty', $this->widget->getFriendlyName()));
		}
		elseif(empty($value))
		{
			throw new Exception($gCms->modules['CMSForms']['object']->lang('field_cannot_be_empty', $this->widget->getFriendlyName()));
		}
		return true;
	}
	
	protected function CheckEqualField()
	{
		global $gCms;
			$value1 = serialize($this->widget->getValues());
			
			try
			{
				$value2 = serialize($this->widget->getForm()->getWidget($this->params)->getValues());
			}
			catch(Exception $e)
			{
				throw new Exception($gCms->modules['CMSForms']['object']->lang('unknown field', $this->params));
				return false;
			}
			
			if ($value1 != $value2)
			{
				throw new Exception($gCms->modules['CMSForms']['object']->lang('fields not equal', $this->widget->getFriendlyName(), $this->widget->getForm()->getWidget($this->params)->getFriendlyName()));
			}
			
			return true;
		
	}
	
	protected function CheckEmail()
	{
		$email = implode('|', $this->widget->getValues());
		if (!self::validEmail($email))
		{
			global $gCms;
			throw new Exception($gCms->modules['CMSForms']['object']->lang('invalid email', $email));
		}
		return true;
	}
	// External functions
	/**
	Validate an email address.
	Provide email address (raw input)
	Returns true if the email address has the email 
	address format and the domain exists.
	*/
	public static function validEmail($email)
	{
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex)
	   {
	      $isValid = false;
	   }
	   else
	   {
	      $domain = substr($email, $atIndex+1);
	      $local = substr($email, 0, $atIndex);
	      $localLen = strlen($local);
	      $domainLen = strlen($domain);
	      if ($localLen < 1 || $localLen > 64)
	      {
	         // local part length exceeded
	         $isValid = false;
	      }
	      else if ($domainLen < 1 || $domainLen > 255)
	      {
	         // domain part length exceeded
	         $isValid = false;
	      }
	      else if ($local[0] == '.' || $local[$localLen-1] == '.')
	      {
	         // local part starts or ends with '.'
	         $isValid = false;
	      }
	      else if (preg_match('/\\.\\./', $local))
	      {
	         // local part has two consecutive dots
	         $isValid = false;
	      }
	      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
	      {
	         // character not valid in domain part
	         $isValid = false;
	      }
	      else if (preg_match('/\\.\\./', $domain))
	      {
	         // domain part has two consecutive dots
	         $isValid = false;
	      }
	      else if
	(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
	                 str_replace("\\\\","",$local)))
	      {
	         // character not valid in local part unless 
	         // local part is quoted
	         if (!preg_match('/^"(\\\\"|[^"])+"$/',
	             str_replace("\\\\","",$local)))
	         {
	            $isValid = false;
	         }
	      }
	      if ($isValid && !(checkdnsrr($domain,"MX") || 
	 checkdnsrr($domain,"A")))
	      {
	         // domain not found in DNS
	         $isValid = false;
	      }
	   }
	   return $isValid;
	}
}