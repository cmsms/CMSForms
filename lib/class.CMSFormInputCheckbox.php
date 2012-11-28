<?php

  /*
    CMSForm Input Checkbox
  */
  
  class CMSFormInputCheckbox extends CMSFormInput
  {
    
    public function initValues()
    {
      if(
        !isset($_REQUEST[$this->id.$this->name])
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
      }
    }
  }