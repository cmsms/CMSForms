<?php

  /*
    CMSForm Input Checkbox
  */
  
  class CMSFormInputCheckbox extends CMSFormInput
  {
    
    public function initValues()
    {
      // var_dump('init checkbox ' . $this->name);
      // var_dump($this->getValues());
      if(
        (!isset($_REQUEST[$this->id.$this->name]) || is_null($_REQUEST[$this->id.$this->name]))
        &&
        (
          isset($_REQUEST[$this->id.'submit'])
          ||
          isset($_REQUEST[$this->id.'apply'])
        )

      )
      {
        // Case when checkbox is unchecked and form is submitted, we should empty the value
        $this->setValues();
        // $this->setValue(-1);
      }
      else
      {
        parent::initValues();
      }
      // var_dump($this->getValues());
      // var_dump($this->getValues());
    }
    
    public function getInput()
    {
      return $this->getModule()->CreateInputCheckbox($this->id, $this->name, '1', (integer)(boolean)$this->getValue());
    }
  }