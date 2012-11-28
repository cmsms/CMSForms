<?php

  /*
    This class aim to handle CMS Forms very differently.
    
    Author: Jean-Christophe Cuvelier <cybertotophe@gmail.com>
    Copyrights: Jean-Christophe Cuvelier 2012
    Licence: GPL    
  
  */

class CMSFormWidget  //extends CmsObject
{
  protected $widget;
  
  public static $fields = array(
    'text'       => array('title' => 'Text field', 'ado' => 'C(255)'),
    'textarea'   => array('title' => 'Text area',   'ado' => 'XL'),
    'checkbox'   => array('title' => 'Checkbox',   'ado' => 'I'),
    'select'     => array('title' => 'Select',     'ado' => 'C(255)'),
    'password'  => array('title' => 'Password',   'ado' => 'C(255)'),
    'date'       => array('title' => 'Date',       'ado' => 'DT'),
    'time'       => array('title' => 'Time',       'ado' => 'DT'),
    'file'       => array('title' => 'File',       'ado' => 'C(255)'));
  
  // REFACTOR
  
  public function __construct(&$form, $id, $module_name, $name, $type,$settings=array()) {
    
    $this->widget = $this->getWidgetObject($type)
                         ->setup($id, $name, $form, $module_name, $settings)
                         ->init();    
    
    
    
    
    // DEPRECATED
    $this->form = $form;
    $this->id = $id;
    $this->module_name = $module_name;
    $this->name = isset($settings['name'])?$settings['name']:$name;
    $this->type = $type;
    $this->settings = $settings;
    //  --
    
    $this->init();
    
    return $this;
  }
  
  protected function getWidgetObject($type)
  {
    switch($type)
    {
      case 'text':
        return new CMSFormInputText();      
      case 'hidden':
        return new CMSFormInputHidden();      
      case 'select':
        return new CMSFormInputSelect();      
      case 'date':
        return new CMSFormInputDate();
      case 'time':
        return new CMSFormInputTime();
      case 'checkbox':
        return new CMSFormInputcheckbox();
      default:
        return new CMSFormInput(); // Useless but it do not break the script
    }
  }
  
  protected function getWidget()
  {
    if (is_object($this->widget))
    {
      return $this->widget;
    }
    else
    {
      return new CMSFormInput(); // Something whent wrong during init TODO:FIX
    }
  }
  
  
  // OLD WAY *****************
  
  // Settings
  protected $id; // The form ID
  protected $module_name;
  protected $name; // The input name
  protected $type;
  protected $input;
  protected $values = array();
  protected $settings = array();
  protected $form_errors;
  protected $show_priority = false;
//  protected $validations = array();
  protected $showned = false;
  protected $form;
  
  protected $is_valid = true;
  protected $template = '%INPUT%';
  

    
  public static $countries = array(); //DEPRECATED CMSFormInputCountries::$countries;
  

  
  // SETUP
  
  public function hide()  {
    $this->type = 'hidden';
    $this->settings['label'] = '';
  }
  
  // FILL
  
  public function refresh() {
    // TODO: Check consequences
    $this->init();
  }
  
  /* CHECK GTD  */
  protected function init() {
    $this->initValues();
  }
  
  /* GTD */
  protected function initValues() {  
      
      switch($this->type)
      {
        case 'checkbox':
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
          break;
        default:
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
          elseif(
            isset($this->settings['object']) && !isset($_REQUEST[$this->id.'submit']) && !isset($_REQUEST[$this->id.'apply']))
          {
            $this->setValues($this->fetchValues());
          }
          elseif(isset($this->settings['preference']) && cms_utils::get_module($this->module_name)->getPreference($this->settings['preference']) != '')
          {
            $this->setValues(cms_utils::get_module($this->module_name)->getPreference($this->settings['preference']));
            //$this->setValues(cms_utils::get_module($this->module_name)->getPreference($this->settings['preference']));
          }

          if(isset($this->settings['default_value']) && !$this->form->isPosted()) 
          {
            if ($this->isEmpty())
            {
              $this->setValues($this->settings['default_value']);
            }
          }
          
          break;
      }
  }

  /* GTD */
  protected function fetchValues()  {
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
  
  // RENDER
  
  public function __toString()  {
    // TODO: REFACTORING
    if ($this->type == 'hidden')
    {
      return $this->getInput();
    }
    else
    {
      $html = '
      <div class="form_widget">
        <div class="form_label"><label for="'.$this->id.$this->name.'">'.$this->getLabel().'</label></div>';            
      if (count($this->form_errors))
      {
        $html .= '<div class="form_errors">' . $this->showErrors() . '</div>';
      }        
      $html .= '
        <div class="form_input">'.$this->getInput().'</div>
      </div>';
      return $html;
    }
  }
  
  public function show($template = null, $force = false)  {
    // TODO: REFACTORING
    
    if ((!$this->showned || $force))
    {
      $html = '';
      if(isset($this->settings['with_div']))
      {
        $class = isset($this->settings['class'])?' class="'.$this->settings['class'].'"':' class="field_'.$this->name.'"';
        $html .= '<div'.$class.'>';
      }
      if (!is_null($template) && ($this->type != 'hidden'))
      {
        $text = str_replace('%FIELDNAME%', $this->name, $template);
        $text = str_replace('%LABEL%', $this->getLabel(), $text);
        $text = str_replace('%LABEL_TAG%', $this->getLabelTag(), $text);
        $text = str_replace('%INPUT%', $this->getInput(), $text);
        $text = str_replace('%ERRORS%', $this->showErrors(), $text);
        $text = str_replace('%TIPS%', $this->getTips(), $text);
        $html .= $text;
      }
      else
      {
        $html .= $this;        
      }    
      if(isset($this->settings['with_div']))
      {
        $html .= '</div>';
      }
      $this->showned = true;
      return $html;
    }
    return null;
  }
  
  public function getName()  {
    return $this->name;
  }
  
  public function getSetting($setting, $default_value = null) {
    return isset($this->settings[$setting])?$this->settings[$setting]:$default_value;
  }
  
  public function setSetting($setting, $value)  {
    $this->settings[$setting] = $value;
  }
  
  public function getForm() {
    if(!is_object($this->form))
    {
      throw new Exception('An error occured retrieving the form object.');
    }
    return $this->form;
  }
  
  
  public function getFriendlyName() {
    if (isset($this->settings['label']))
    {
      return $this->settings['label'];
    }
    // Try to get it from language file
    
    if (cms_utils::get_module($this->module_name))
    {
      return cms_utils::get_module($this->module_name)->lang('form_'.$this->name);
    }
    return null;
  }
    
  public function getLabel()  {
    return $this->getFriendlyName();
  }
  
  public function getLabelTag() {
    return '<label for="'.$this->id.$this->name.'">' . $this->getLabel() . '</label>';
  }
  
  public function getTips() {
    if (isset($this->settings['tips']))
    {
      return $this->settings['tips'];
    }
    // Try to get it from language file
    
    if (cms_utils::get_module($this->module_name))
    {
      // TODO: This should be shown only it the lang key exists...
      //return cms_utils::get_module($this->module_name)->lang('tips_'.$this->name);
    }
    return null;
  }
  
  public function getInput()  {  
    if (!empty($this->input))
    {
      return $this->input;
    }
    
    
    if (cms_utils::get_module($this->module_name))
    {
      switch($this->type)
      {
        case 'text':
          return $this->getWidget()->getInput();
          // return $this->CreateInputText();        
        case 'hidden':
          return $this->getWidget()->getInput();
          // return cms_utils::get_module($this->module_name)->CreateInputHidden($this->id, $this->name, $this->getValue());
        case 'select':
          return self::CreateSelector($this->id, $this->name, $this->getValues(), $this->settings);
        case 'countries':
          return self::CreateCountriesSelector($this->id, $this->name, $this->getValues(), $this->settings);
        case 'checkbox':
          return cms_utils::get_module($this->module_name)->CreateInputCheckbox($this->id, $this->name, '1', (integer)$this->getValue());
        case 'textarea':
          if (isset($this->settings['show_wysiwyg']) && $this->settings['show_wysiwyg'] == true)
          {
            return cms_utils::get_module($this->module_name)->CreateTextArea(true, $this->id, $this->getValue(), $this->name, $this->getSetting('class'), $this->getSetting('htmlid'));            
          }
          else
          {
            return cms_utils::get_module($this->module_name)->CreateTextArea(
              false,
              $this->id,
              $this->getValue(), 
              $this->name,
              isset($this->settings['classname'])?$this->settings['classname']:'',
              isset($this->settings['htmlid'])?$this->settings['htmlid']:'',
              isset($this->settings['encoding'])?$this->settings['encoding']:'',
              isset($this->settings['stylesheet'])?$this->settings['stylesheet']:'',
              isset($this->settings['cols'])?$this->settings['cols']:'80',
              isset($this->settings['rows'])?$this->settings['rows']:'15',            
              isset($this->settings['forcewysiwyg'])?$this->settings['forcewysiwyg']:'',          
              isset($this->settings['wantedsyntax'])?$this->settings['wantedsyntax']:'',            
              isset($this->settings['addtext'])?$this->settings['addtext']:''    
              );
          }
        case 'codearea':
          return cms_utils::get_module($this->module_name)->CreateSyntaxArea($this->id, $this->getValue(), $this->name,'pagebigtextarea', '','', '', 90, 15);
        case 'time':
          return self::CreateTimeSelect($this->id,$this->name,$this->getValues());
        case 'date':
          return self::CreateDateSelect($this->id,$this->name,$this->getValues(), $this->settings);
        case 'pages':
          $this->settings['values'] = array(0 => '&laquo; ' . cms_utils::get_module('CMSForms')->lang('select one') . ' &raquo;') + self::getPagesList($this->id, $this->name,$this->getValue(), $this->settings);;
          return self::CreateSelector($this->id, $this->name, $this->getValues(), $this->settings);

        case 'static':
          return $this->getValue();
        case 'file':
          return $this->getUploadField();
        case 'password':
          return cms_utils::get_module($this->module_name)->CreateInputPassword($this->id, $this->name, $this->getValue()
          ,isset($this->settings['size'])?$this->settings['size']:20);
        default:
          return null;
      }
    }
    return null;
    
  }
  
  public function CreateInputText()
  {
    $input = cms_utils::get_module($this->module_name)->CreateInputText($this->id, $this->name, $this->getValue(), isset($this->settings['size'])?$this->settings['size']:80, isset($this->settings['maxlength'])?$this->settings['maxlength']:255);
     
    if(isset($this->settings['classname']))
    {
      // Overload this "##@!§$#" class system
      // In french in the text: Nom de Dieu de putain de bordel de merde de saloperie de connard d'enculé de ta mère
      // (http://www.imdb.com/title/tt0234215/trivia?tab=tr&item=tr0752382)
      // (Sorry for prude ears, this is just a comment in a source code...)
      $input = str_replace('class="cms_textfield"', 'class="'.$this->settings['classname'].'"', $input);
    }
    
    return $input;
  }

  // PROCESS
  
  public function process($save = true) {
    // Validate
    $this->validate();
    
    // Save values
    if ($save == true)
    {
      $this->save();
    }
  }
  
  // VALIDATION
  
  protected function validate() {
    if (isset($this->settings['validators']) && is_array($this->settings['validators']))
    {
      //var_dump($this->settings['validators']);
      // CHANGE THAT
      
      foreach ($this->settings['validators'] as $validator => $value)
      {
        $validate = new CMSFormValidator($this,$validator,$value);
        try
        {
          if($validate->check() === false) $this->is_valid = false;
        }
        catch(Exception $e)
        {
          $this->setError($e->getMessage(), 'form error');
        }
        
      }
    }
  }
  
  public function isValid() {
    if (($this->is_valid == false ) || $this->hasErrors())
    {
      return false;
    }
    return true;
  }
  
  public function setValidator($validator, $params = array()) {
    return $this->addValidator($validator, $params);
  }
  
  public function addValidator($validator, $params = array()) {
    $this->settings['validators'][$validator] = $params;
  }
  
  public function removeValidator($validator) {
    unset($this->settings['validators'][$validator]);
  }
  
  // SAVE
  
  protected function save() {
    if($this->isValid() == true)
    {
      if (isset($this->settings['object']))
      {
        $this->saveObject();
      }
      if (isset($this->settings['preference']))
      {
        $this->savePreference();
      }
    }
  }
  
  protected function saveObject() {
    if(is_object($this->settings['object']) && $this->type != 'file')
    {
      // This do not save the object state, so we have to do it outside the form

      if ($this->type == 'date')
      {
        //$values = $this->values; Always save it as a string
        if (count($this->values) > 1)
        {          
          $values = $this->values['0'] . '-' . $this->values['1'] . '-' . $this->values['2'];
        }
        else
        {
          $values = $this->getValue();
        }
      }
      elseif ($this->type == 'time')
      {
        //$values = $this->values; Always save it as a string
        if (count($this->values) > 1)
        {          
          $values = $this->values['0'] . ':' . $this->values['1'] . ':' . $this->values['2'];
        }
        else
        {
          $values = $this->getValue();
        }
      }
      else
      {
        $values = $this->getValue();
      }    
    
      if (isset($this->settings['set_method']))
      {
        $this->settings['object']->{$this->settings['set_method']}($values);
      }
      else
      {
        if (isset($this->settings['field_name']))
        {
          $name = $this->settings['field_name'];
        }
        else
        {
          $name = $this->name;
        }
        
        if(method_exists($this->settings['object'], 'set'))
        {    
          $this->settings['object']->set($name, $values);
        }
        else
        {
          try
          {
            $this->settings['object']->$name = $values;
          }
          catch(Exception $e)
          {
            die('unable to do');
          }
        }
        
        
      }
    }
  }
  
  protected function savePreference() {
    if(isset($this->settings['preference']) && !isset($_REQUEST[$this->id.'cancel']))
    {
      // Check if there is no cancel button first because we save the value directly !
      cms_utils::get_module($this->module_name)->setPreference($this->settings['preference'], $this->getValue());
    }
  }
  
  // ACCESS
  
  public function getValue()  {
    if (($this->type == 'date') &&  (count($this->values) > 1))
    {
      return $this->values['0'] . '-' . $this->values['1'] . '-' . $this->values['2'];
    }  
    elseif (($this->type == 'time') &&  (count($this->values) > 1))
    {
      return $this->values['0'] . ':' . $this->values['1'] . ':' . $this->values['2'];
    }
    elseif (count($this->values) == 1)
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
      return true;
      return false;
  }
    
  public function getStringValue()  {
    //DEPRECATED
    return $this->getValue();
  }

  public function getValuesToString() {
    // DEPRECATED
    return (string)$this->getValue();
  }
  
  public function isEmptyValues() {
    //DEPRECATED
    
    if (is_array($this->values))
    {
      return $this->isEmpty();
    }
    elseif(empty($this->values))
    {
      return true;
    }
    return false;
  }
  
  // DIVERS
  
  public static function getFieldsList()  {
    $list = array();
    foreach(self::$fields as $type => $field)
    {
      $list[$type] = $field['title'];
    }
    return $list;
  }
  
  public static function getAdoType($type)  {
    if (isset(self::$fields[$type]['ado']))
    {
      return self::$fields[$type]['ado'];
    }
    else
    {
      return 'C(255)'; // Better than nothing...
    }
  }
        
  public function getUploadField()  {
    $field = cms_utils::get_module($this->module_name)->CreateInputFile($this->id, $this->name, '', isset($this->settings['size'])?$this->settings['size']:30);
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
        if ((substr($file_url, -1) != '/') && (substr($this->getValue(),0,1) != '/')) $file_url .'/';
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
        $field .= ' ' .  cms_utils::get_module($this->module_name)->CreateInputCheckbox($this->id, $this->settings['delete_checkbox'], '1') . ' ' .  cms_utils::get_module($this->module_name)->lang('delete');
      }
    }
    
  
    
    $html .= $field . '</span>';
    return $html;
  }
  
  // Tools
  
  public static function getPagesList($id,$name,$value,$settings = array()) {
    
    $pages = cmsms()->GetContentOperations()->GetAllContent();
    $array = array();
    
    if (isset($settings['childrenof']))
    {
      $childrenof = self::getPageId($settings['childrenof']);
    }
  
    if (isset($settings['start_page']))
    {
      $start_page = self::getPageId($settings['start_page']);
    }
    
    foreach($pages as $page)
    {
      if (
        (!isset($start_page) && !isset($childrenof)) 
        ||
        (isset($childrenof) && ($page->ParentId() == $childrenof)) // List of all childrens
        ||
        (isset($start_page) && (strpos($page->IdHierarchy(), $start_page.'.') === 0)) // List of all descendants
        )
      {
        $array[$page->Id()] = $page->Hierarchy().'. - '.$page->Name();
      }
      
    }
        
    return $array;
  }
  
  public static function getPageId($alias)  {
    $manager = cmsms()->GetHierarchyManager();
    $node = $manager->sureGetNodeByAlias($alias);
    if ($node) {
        $content = $node->GetContent();
        if ($content)
        {
            return $content->Id();
        }
    } else {
        $node = $manager->sureGetNodeById($alias);
        if ($node) {
          return $alias;
        }
    }
    return null;
  }
  
  public static function createPageSelect($id,$name,$value,$settings = array()) {
    $pages = cmsms()->GetContentOperations()->GetAllContent();
    
    $html = '<select name="'.$id.$name.'"><option>'.cms_utils::get_module('CMSForms')->lang('select one').'</option>';
    
    foreach($pages as $page)
    {
      $html .= '<option value="' . $page->Id() . '"';
      
      if($value == $page->Id())
      {
        $html .= ' selected="selected"';
      }      
      
      $html .= '>' . $page->Hierarchy().'. - '.$page->Name() . '</option>';
    }
    
    $html .= '</select>';    
    
    return $html;
  }
  
  public static function createDateSelect($id,$name,$values,$settings)  {
    if (count($values) == 1)
    {
      if (strpos($values[0], '-') !== false) $values = explode('-',$values[0]);
    }
    $start_year = isset($settings['start_year'])?$settings['start_year']:date('Y');
    $number_years = isset($settings['number_years'])?$settings['number_years']:20;
    $end_year = $start_year + $number_years;
    
    $year = self::CreateInputSelectList($id,$name.'[0]',self::CreateNumberList($end_year,$start_year),array($values[0]),1,'',false);
    $month = self::CreateInputSelectList($id,$name.'[1]',self::CreateMonthsList(),array($values[1]),1,'',false);
    $day = self::CreateInputSelectList($id,$name.'[2]',self::CreateNumberList(31,1),array($values[2]),1,'',false);
    if (isset($settings['european_date']))
    {
      return $day . $month . $year;
    }
    return $year . $month . $day;
  }
  
  public static function CreateTimeSelect($id,$name,$values)  {
    if (count($values) == 1)
    {
      if (strpos($values[0], ':') !== false) $values = explode(':',$values[0]);
    }
    $hours = self::CreateInputSelectList($id,$name.'[0]',self::CreateNumberList(23),array($values[0]),1,'',false);
    $minutes = self::CreateInputSelectList($id,$name.'[1]',self::CreateNumberList(59),array($values[1]),1,'',false);
    $seconds = self::CreateInputSelectList($id,$name.'[2]',self::CreateNumberList(59),array($values[2]),1,'',false);
    return $hours . ' : ' . $minutes . ' : ' . $seconds;
  }

  public static function CreateMonthsList() {
    $months = array();
    for ($i = 1; $i <= 12; ++$i) {
      $t = mktime(0, 0, 0, $i, 1, 2000);
      $months[$i] = date('M', $t);
    }
    return $months;
  }

  public static function CreateNumberList($end, $start=0) {
    if (($end < 0)||(!is_numeric($end))) $end = 1;
    $list = array();
    for ($i = $start; $i <= $end; $i++)
    {
      $list[$i] = (string)$i;
    }
    return $list;
  }
  
  public static function CreateCountriesSelector($id,$name,$values,$settings) {
    $settings['values'] = CMSFormInputCountries::$countries;
    return self::CreateSelector($id,$name,$values,$settings);
  }
  
  public static function CreateSelector($id,$name,$values,$settings)  {
    
    return CMSFormInputSelect::CreateSelector($id,$name,$values,$settings);
    
    // DEPRECATED
    if(isset($settings['expanded']) && $settings['expanded'] == true)
    {
      return self::CreateInputExpandedList($id, $name, isset($settings['values'])?$settings['values']:array(), $values, isset($settings['addtext'])?$settings['addtext']:'', isset($settings['multiple'])?true:false, $settings);
    }
    else
    {
      $items = isset($settings['values'])?$settings['values']:array();
      if(isset($settings['include_custom']))
      {
        $items = array('' => $settings['include_custom']) + $items;
      }
      return self::CreateInputSelectList($id, $name, $items, $values, isset($settings['size'])?$settings['size']:1, '', isset($settings['multiple'])?true:false);
    }
  }
  
  public static function CreateInputSelectList($id, $name, $items, $selecteditems=array(), $size=3, $addttext='', $multiple = true) {
    
    return CMSFormInputSelect::DeprecatedCreateInputSelectList($id, $name, $items, $selecteditems, $size, $addttext, $multiple);
    
    $id = cms_htmlentities($id);
    $name = cms_htmlentities($name);
    $size = cms_htmlentities($size);
    $multiple = cms_htmlentities($multiple);
  
    if($multiple == true)
    {
      $name .= '[]';
    }
  
    $text = '<select name="'.$id.$name.'" id="'.$id.$name.'"';
    if ($addttext != '')
    {
      $text .= ' ' . $addttext;
    }
    if( $multiple )
      {
      $text .= ' multiple="multiple" ';
      }
      
      if ($size > 1)
      {
        $text .= ' size="'.$size.'"';
      }
    
    $text .= '>';
    
    $count = 0;
    foreach ($items as $key=>$value)
    {
      if (is_array($value))
       {
        $text .= '<optgroup label="' . $key . '">';
        foreach ($value as $key2 => $entry)
        {
          $text .= self::generateOption($key2, $entry, $selecteditems);
          $count++;
        }
        $text .= '</optgroup>';
      }
      else
      {
        $text .= self::generateOption($key, $value, $selecteditems);
        $count++;
      }
    }
    $text .= '</select>'."\n";
  
    return $text;
  }
  
  // protected static function generateOption($key, $value, $selecteditems)  {
  //   // if (is_array($value))
  //   //    {
  //   //     $array = array_shift($value);
  //   //     $key = implode('|',array_keys($array));
  //   //     $value = implode('|', $array);
  //   //   }
  // 
  //   //$value = cms_htmlentities($value);
  //   $value = $value;
  // 
  //   $text = '<option value="'.$key.'"';
  //   if (in_array($key, $selecteditems))
  //   {
  //     $text .= ' ' . 'selected="selected"';
  //   }
  //   $text .= '>';
  //   $text .= $value;
  //   $text .= '</option>';
  //   
  //   return $text;  
  // }
  
  public static function CreateInputExpandedList($id, $name, $items, $selecteditems=array(), $addttext='', $multiple = true, $params = array()) {
    
    return CMSFormInputSelect::DeprecatedCreateInputExpandedList($id, $name, $items, $selecteditems, $addttext, $multiple, $params);
    
    $id = cms_htmlentities($id);
    $name = cms_htmlentities($name);
    $multiple = cms_htmlentities($multiple);
  
    $list = array();
    foreach($items as $key => $item)
    {
      if (is_array($item))
      {
        $list[] = array('label' => '<strong>' . $key . '</strong>', 'input' => '');
  
        foreach($item as $key2 => $entry)
        {
           self::generateExpandedList($list, $id, $entry, $name, $key2, $selecteditems, $multiple, $addttext);
        }
        
      }
      else
      {
        self::generateExpandedList($list, $id, $item, $name, $key, $selecteditems, $multiple, $addttext);
      }
    }
    
    if(isset($params['mode']) && ($params['mode'] == 'html'))
    {
      // TODO
      return 'Not implemented yet';
    }
    elseif(isset($params['mode']) && ($params['mode'] == 'array'))
    {
      return $list;
    }
    else
    {
      $html = '';
      if (count($list) > 0)
      {
        $html .= '<ul>';
        foreach($list as $item)
        {
          $html .= '<li>'.$item['input']. ' '. $item['label'] .'</li>';
        }
        $html .= '</ul>';
      }
      return $html;
    }
  
  }
  
  // protected static function generateExpandedList(&$list, $id, $item, $name, $key, $selecteditems, $multiple, $addttext) {
  //   if (in_array($key, $selecteditems))
  //   {
  //     $text = ' checked="checked"' . ' ' . $addttext;
  //   }
  //   else
  //   {
  //     $text = ' ' . $addttext;
  //   }
  //   
  //   if($multiple)
  //   {        
  //     $list[] = array(
  //       'label' => '<label for="'.$id.$name.'['.$key.']">' . $item . '</label>',
  //       'input' => '<input type="checkbox" name="'.$id.$name.'['.$key.']" id="'.$id.$name.'['.$key.']" value="'.$key.'"'.$text.' />'
  //       );
  //   }
  //   else
  //   {
  //     $list[] = array(
  //       'label' => '<label for="'.$id.$name.$key.'">' . $item . '</label>',
  //       'input' => '<input type="radio" name="'.$id.$name.'" id="'.$id.$name.$key.'" value="'.$key.'"'.$text.' />'
  //       );
  //   }
  //   return $list;
  // }
  
  //
  
  public static function isImage($filename) {
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

  // ERRORS
    
  public function hasErrors() {
    if (count($this->form_errors) == 0)
    {
      return false;
    }
    return true;
  }

  public function noError() {
    return !$this->hasErrors();
  }

  public function getErrors() {
    return $this->form_errors;
  }

  public function showErrors()  {
    $html = '';
    if(count($this->form_errors) > 0)
    {
      $html .= '<ul class="form_widget_errors">';
      foreach($this->form_errors as $priority => $errors)
      {
        $html .= '<li>';
        if ($this->show_priority) $html .= '<em class="form_widget_error_priority">'.$priority.'</em>';
        $html .= '<ul>';
        foreach($errors as $error)
        {
          $html .= '<li class="form_widget_error_message">'.$error.'</li>';
        }        
        $html .= '</ul></li>';
      }
      $html .= '</ul>';
    }
    return $html;
  }

  public function setError($message,$priority='default')  {
    $this->form_errors[$priority][] = $message;
  }

}