<?php

  /*
    CMSForm Input Textarea
  */
  
  class CMSFormInputTextarea extends CMSFormInput
  {
    public function getInput()
    {
      if (isset($this->settings['show_wysiwyg']) && $this->settings['show_wysiwyg'] == true)
      {
        return $this->getModule()->CreateTextArea(true, $this->id, $this->getValue(), $this->name, $this->getSetting('class'), $this->getSetting('htmlid'));            
      }
      else
      {
        return $this->getModule()->CreateTextArea(
          false,
          $this->id,
          $this->getValue(), 
          $this->name,
          $this->getSetting('classname',''),
          $this->getSetting('htmlid',''),
          $this->getSetting('encoding',''),
          $this->getSetting('stylesheet',''),
          $this->getSetting('cols','80'),
          $this->getSetting('rows','15'),
          $this->getSetting('forcewysiwyg',''),
          $this->getSetting('wantedsyntax',''),
          $this->getSetting('addtext','')
          );
      }
    }
  }