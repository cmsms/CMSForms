<?php

/*
	Module: CMS Forms - This module helps developers to handle forms for their frontend or backend applications
	
	Copyrights: Jean-Christophe Cuvelier - 2012 ©
*/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'class.CMSFormPlugins.php');

class CMSForms extends CMSModule
{
	public function GetName()               { return 'CMSForms';	               }
	public function GetVersion()            { return '1.11.0';                    }
	public function GetAuthor()             { return 'Jean-Christophe Cuvelier'; }
	public function GetAuthorEmail()        { return 'jcc@atomseeds.com';   }
	
    public  function GetHelp() {              return $this->Lang('help');  }
	public function MinimumCMSVersion()    { return '1.10';  }

    public function InitializeFrontend()
    {
        CMSFormPlugins::RegisterPlugins($this->smarty);
    }

    public function InitializeAdmin()
    {
        CMSFormPlugins::RegisterPlugins($this->smarty);
    }
}
