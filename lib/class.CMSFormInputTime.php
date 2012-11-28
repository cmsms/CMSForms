<?php

  /*
    CMSForm Input Time
  */
  
  class CMSFormInputTime extends CMSFormInput
  {
    
    public function getValue()  {
      if ((count($this->values) > 1))
      {
        return $this->values['0'] . ':' . $this->values['1'] . ':' . $this->values['2'];
      }
      else
      {
        return null;
      }
    }    
  }