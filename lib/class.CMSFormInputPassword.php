<?php

  /*
    CMSForm Input Password
  */
  
  class CMSFormInputPassword extends CMSFormInput
  {
    public function getInput()
    {
      return $this->getModule()->CreateInputPassword($this->id, $this->name, '',$this->getSetting('size', 20));
    }

    public function save() {
			if($this->getValue() != '')
      {
				parent::save();
			}
		}
  }