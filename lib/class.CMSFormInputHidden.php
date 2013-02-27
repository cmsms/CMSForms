<?php

  /*
    CMSForm Input Hidden
  */
  
  class CMSFormInputHidden extends CMSFormInput
  {
    public function __toString()  {
      return $this->getInput();
    }
    
    public function getInput()
    {
      return $this->getModule()->CreateInputHidden($this->id, $this->name, $this->getValue());
    }
  }