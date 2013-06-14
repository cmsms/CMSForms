<?php

  /*
    CMSForm Input File
  */
  
  class CMSFormInputFile extends CMSFormInput
  {
    public function getInput()
    {
      return $this->getUploadField();
      // return $this->getModule()->CreateInputHidden($this->id, $this->name, $this->getValue());
    }
  
   protected function saveObject() 
   {
     // TODO: This case
   }
    
    protected function getUploadField()  {
      $field = $this->getModule()->CreateInputFile($this->id, $this->name, '', $this->getSetting('size', 30));
      
      $html = '<span>';
      if (!$this->isEmpty())
      {
        if(isset($this->settings['direct_link']) && $this->settings['direct_link'] != '')
        {
          $file_url = $this->settings['direct_link'];
        }
        else
        {
          $file_url = isset($this->settings['base_url'])?$this->settings['base_url']:'';
          if ((substr($file_url, -1) != '/') && (substr($this->getValue(),0,1) != '/')) $file_url .= '/';
          $file_url .= $this->getValue();
          $file_url = str_replace(DIRECTORY_SEPARATOR, '/', $file_url);
        }

        if (self::isImage($this->getValue()))
        {
          $text = '<img src="'.$file_url.'" />';
        }
        else
        {
          $text = basename($this->values[0]);
        }

        $html .= '<span style="display:block; margin-bottom: 7px;"><a href="'.$file_url. '" rel="external" >'. $text .'</a></span> ';

        if (isset($this->settings['delete_checkbox']))
        {
          $field .= ' ' .  $this->getModule()->CreateInputCheckbox($this->id, $this->settings['delete_checkbox'], '1') . ' ' .  $this->getModule()->lang('delete');
        }
      }



      $html .= $field . '</span>';
      return $html;
    }
    
    protected static function isImage($filename) {
      $valid_extensions = array('jpeg','jpg','gif','png');
      if (in_array(self::getFileExtension($filename),$valid_extensions))
      {
        return true;
      }
      return false;
    }

    public static function getFileExtension($filename)  {
      $file = explode('.', $filename);
        if (count($file) > 1)
        {
          return strtolower(end($file));
        }
        else 
        {
          return null;
        }
    }
    
  }