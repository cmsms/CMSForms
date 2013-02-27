<?php

/*
	Module: CMS Forms - This module helps developers to handle forms for their frontend or backend applications
	
	Copyrights: Jean-Christophe Cuvelier - 2012 ©
*/

// require_once dirname(__FILE__) . '/lib/classes/CMSForm.class.php';
// require_once dirname(__FILE__) . '/lib/classes/CMSFormWidget.class.php';
// require_once dirname(__FILE__) . '/lib/classes/CMSFormValidator.class.php';

class CMSForms extends CMSModule
{
	public function GetName()               { return 'CMSForms';	               }
	public function GetVersion()            { return '1.0.8';                    }
	public function GetAuthor()             { return 'Jean-Christophe Cuvelier'; }
	public function GetAuthorEmail()        { return 'cybertotophe@gmail.com';   }
	
  public  function GetHelp() {              return $this->Lang('help');  }
  /* Comment from Cloud9 IDE */
}
