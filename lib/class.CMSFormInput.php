<?php

  /*
    CMSForm Input base
  */
  
  class CMSFormInput
  {
    protected $id;
    protected $name;
    protected $values = array(); // We always store results in an array. In case of single field, the single value is array[0]
    
    protected $form;
    protected $module_name;
    protected $settings = array();
    
    public function __construct()
    {
      return $this;
    }
    
    public function setup($id, $name, &$form, $module_name, $settings = array())
    {
      $this->id = $id;
      $this->name = isset($settings['name'])?$settings['name']:$name;
      $this->form = $form;
      $this->module_name = $module_name;
      $this->settings = $settings;
      
      return $this;
    }
    
    public function init()
    {
      // Initialize the field
      
      $this->initValues();
      
      return $this;
    }
    
    public function getForm() {
      if(!is_object($this->form))
      {
        throw new Exception('An error occured retrieving the form object.');
      }
      return $this->form;
    }

    public function getModule() {
      if($module = cms_utils::get_module($this->module_name))
      {
        return $module;
      }
      else
      {
        return cms_utils::get_module('CMSForms'); // Default safeback
      }
    }
    
    // ##### FORM #####
    
    public function getInput() {
      return null;
    }
    
    // ##### VALUES #####
    
    // Values manipulation
    
    public function initValues() {
      if (isset($_REQUEST[$this->id.$this->name]))
      {
        if (is_array($_REQUEST[$this->id.$this->name]))
        {
          $this->setValues($_REQUEST[$this->id.$this->name]);
        } 
        else
        {
          $this->setValues(html_entity_decode($_REQUEST[$this->id.$this->name]));
        }
      }
      elseif(isset($this->settings['value']))
      {
        $this->setValues($this->settings['value']);
      }
      elseif(isset($this->settings['object']) && !$this->getForm()->isPosted())
      {
        $this->setValues($this->fetchObjectValues());
      }
      elseif(isset($this->settings['preference']) && $this->getModule()->getPreference($this->settings['preference']) != '')
      {
        $this->setValues($this->getModule()->getPreference($this->settings['preference']));
      }

      if(isset($this->settings['default_value']) && !$this->getForm()->isPosted()) 
      {
        if ($this->isEmpty())
        {
          $this->setValues($this->settings['default_value']);
        }
      }
    }
    
    protected function fetchObjectValues()  {
      if(isset($this->settings['object']) && is_object($this->settings['object']))
      {
        if (isset($this->settings['get_method']))
        {
          return $this->settings['object']->{$this->settings['get_method']}();
        }
        else
        {
          $name = isset($this->settings['field_name'])?$this->settings['field_name']:$this->name;

          if(method_exists($this->settings['object'], 'get'))
          {
            return $this->settings['object']->get($name);
          }
          else
          {
            try
            {
              return $this->settings['object']->$name;
            }
            catch(Exception $e)
            {
              //  die('unable to do'); // TODO: Treath error
            }
          }
        }      
      }
    }
    
    public function resetValues() {
      $this->values = array();
    }
    
    public function processValues() {
      
    }
    
    public function getValue()  {       
      if (count($this->values) == 1)
      {
        reset($this->values);
        return (string) current($this->values);
      }
      else
      {
        return (string) implode('|||',$this->values);
      }    
    }

    public function setValue($value, $key = 0)  {
      $this->values[$key] = (string)$value;
    }

    public function removeValueIfEqual($value)  {
      if($this->getValue() == $value)
      {
        $this->setValues(array());
      }
    }

    public function getValues() {
      return $this->values;
    }

    public function setValues($values = array())  {
      if (is_array($values))
      {
        $this->values = $values;
      }
      elseif (strpos($values, '|||') !== false)
      {
        $this->values = explode('|||', $values);
      }
      else
      {
        $this->values[0] = $values;
      }    
    }  

    public function setDefaultValues($values) {
      if (is_array($values))
      {
        $this->values = $values;
      }
      else
      {
        $this->values = array($values);
      }
    }

    public function isEmpty() {
      if ((count($this->values) == 1) && (empty($this->values[0])) || (count($this->values) == 0))
      {
        return true;
      }
      else
      {
        return false;
      }
    }
    
    // Values validation
  }