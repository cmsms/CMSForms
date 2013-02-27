<?php

  /*
    CMSForm Input Password
  */
  
  class CMSFormInputPassword extends CMSFormInput
  {
    public function getInput()
    {
      return $this->getModule()->CreateInputPassword($this->id, $this->name, $this->getValue()
      ,$this->getSetting('size', 20));
    }
  }