<?php

  /*
    CMSForm Input CMSFormInputSyntaxarea
  */
  
  class CMSFormInputSyntaxarea extends CMSFormInput
  {
    public function getInput()
    {
      return $this->getModule()->CreateSyntaxArea($this->id, $this->getValue(), $this->name,'pagebigtextarea', '','', '', 90, 15);
    }
  }