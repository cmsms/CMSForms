<?php

/*
	Module: CMS Forms - This module helps developers to handle forms for their frontend or backend applications
	
	Copyrights: Jean-Christophe Cuvelier - Morris & Chapman Belgium - 2010 ©
*/

require_once dirname(__FILE__) . '/lib/classes/CMSForm.class.php';
require_once dirname(__FILE__) . '/lib/classes/CMSFormWidget.class.php';
require_once dirname(__FILE__) . '/lib/classes/CMSFormValidator.class.php';

class CMSForms extends CMSModule
{
	public function GetName()               { return 'CMSForms';	               }
	public function GetVersion()            { return '0.1.2';                    }
	public function GetAuthor()             { return 'Jean-Christophe Cuvelier'; }
	public function GetAuthorEmail()        { return 'cybertotophe@gmail.com';   }
    /* Comment from Cloud9 IDE */
}
