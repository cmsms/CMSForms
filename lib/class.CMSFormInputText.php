<?php

  /*
    CMSForm Input Text
  */
  
  class CMSFormInputText extends CMSFormInput
  {
    public function getInput()
    {
      $input = $this->getModule()->CreateInputText($this->id, $this->name, $this->getValue(), isset($this->settings['size'])?$this->settings['size']:80, isset($this->settings['maxlength'])?$this->settings['maxlength']:255);

      if(isset($this->settings['classname']))
      {
        $input = str_replace('class="cms_textfield"', 'class="'.(string)$this->settings['classname'].'"', $input);
      }

      if(isset($this->settings['placeholder']))
      {
        $input = str_replace('>', 'placeholder="'.(string)$this->settings['placeholder'].'">', $input);
      }

      return $input;
    }
  }