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
    'datetime'       => array('title' => 'Date & Time',	'ado' => 'I'),
    'file'       => array('title' => 'File',       'ado' => 'C(255)'));

  const FIELD_TEXT = 'text';

  // REFACTOR
  
  public function __construct(&$form, $id, $module_name, $name, $type,$settings=array()) {
    
    $this->widget = $this->getWidgetObject($type)
                         ->setup($id, $name, $form, $module_name, $settings)
                         ->init();
    // var_dump($this->widget);
    // DEPRECATED
    // $this->form = $form;
    // $this->id = $id;
    // $this->module_name = $module_name;
    // $this->name = isset($settings['name'])?$settings['name']:$name;
    // $this->type = $type;
    // $this->settings = $settings;
    // //  --
    // 
    // $this->init();
    
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
      case 'pages':
        return new CMSFormInputPages();      
      case 'countries':
        return new CMSFormInputCountries();      
      case 'textarea':
        return new CMSFormInputTextarea();      
      case 'codearea':
        return new CMSFormInputSyntaxarea();      
      case 'date':
        return new CMSFormInputDate();
			case 'datetime':
        return new CMSFormInputDateTime();
      case 'time':
        return new CMSFormInputTime();
      case 'checkbox':
        return new CMSFormInputCheckbox();
      case 'password':
        return new CMSFormInputPassword();
      case 'file':
        return new CMSFormInputFile();
      case 'static':
        return new CMSFormInputStatic();
      default:
        $class = 'CMSFormInput' . $type;
        if(class_exists($class))
        return new $class();
        $class = 'CMSFormInput' . ucfirst($type);
        if(class_exists($class))
        return new $class();
        return new CMSFormInput();
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


  public function __get($name)  {
    if(isset($this->$name))
    {
        return $this->$name;
    }
    else
    {
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
  }

  // SETUP
  public function hide()  {
    
    $this->getWidget()->setSetting('label','');
    
    $widget = new CMSFormInputHidden();
    $widget->setup(
            $this->getWidget()->id,
            $this->getWidget()->name,
            $this->getWidget()->form,
            $this->getWidget()->module_name,
            $this->getWidget()->settings
          )
          ->init()
          ->setValues($this->getWidget()->getValues());
    $this->widget = $widget;
  }

  // FILL

  
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
      return null;
  }
  
  // LINKED TO WIDGETS  
  

  
  public function show($template = null, $force = false)  {
   return $this->getWidget()->show($template,$force);
  }
  
  public function __toString()  {
    return $this->getWidget()->__toString();
  }
  
  public function getLabel()  {
    return $this->getWidget()->getLabel();
  }
  
  public function getLabelTag() {
    return $this->getWidget()->getLabelTag();
  }
  
  public function getTips() {
    return $this->getWidget()->getTips();
  }
  
  public function refresh() {
    // TODO: Check consequences
    $this->getWidget()->init();
  }
  
  public function getName()  {
    return $this->getWidget()->getName();
  }
  
  public function getSetting($setting, $default_value = null) {
    return $this->getWidget()->getSetting($setting, $default_value);
  }
  
  public function setSetting($setting, $value)  {
    $this->getWidget()->setSetting($setting, $value);
  }
  
  public function getForm() {
    return $this->getWidget()->getForm();
  }
  
  public function getFriendlyName() {
    return $this->getWidget()->getFriendlyName();
  }
    
  public function getInput()  {  
    if (!empty($this->input))
    {
      return $this->input;
    }
    
    return $this->getWidget()->getInput();
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
    $this->getWidget()->process($save);
  }
  
  // VALIDATION
  
  public function isValid() {
    return $this->getWidget()->isValid();
  }
  
  public function setValidator($validator, $params = array()) {
    return $this->getWidget()->setValidator($validator, $params);
  }
  
  public function addValidator($validator, $params = array()) {
    $this->getWidget()->addValidator($validator, $params);
  }
  
  public function removeValidator($validator) {
    $this->getWidget()->removeValidator($validator);
  }
  
  // ACCESS
  
  public function getValue()  {
    return $this->getWidget()->getValue(); 
  }
  
  public function setValue($value, $key = 0)  {
    $this->getWidget()->setValue($value,$key);
  }

  public function removeValueIfEqual($value)  {
    $this->getWidget()->removeValueIfEqual($value);
  }
  
  public function getValues() {
    return $this->getWidget()->getValues();
  }
  
  public function setValues($values = array())  {
    $this->getWidget()->setValues($values);  
  }  
  
  public function setDefaultValues($values) {
    $this->getWidget()->setDefaultValues($values);
  }
    
  public function isEmpty() {
    return $this->getWidget()->isEmpty();
  }
    
  public function getStringValue()  {
    //DEPRECATED
    return $this->getWidget()->getValue();
  }

  public function getValuesToString() {
    // DEPRECATED
    return (string)$this->getWidget()->getValue();
  }
  
  // public function isEmptyValues() {
  //   //DEPRECATED
  //   
  //   if (is_array($this->values))
  //   {
  //     return $this->isEmpty();
  //   }
  //   elseif(empty($this->values))
  //   {
  //     return true;
  //   }
  //   return false;
  // }
  
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
        
  // Tools (DEPRECATED ==> SEE CLASSES)
  
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
    return CMSFormInputDate::CreateNumberList($end, $start);
    
    // if (($end < 0)||(!is_numeric($end))) $end = 1;
    // $list = array();
    // for ($i = $start; $i <= $end; $i++)
    // {
    //   $list[$i] = (string)$i;
    // }
    // return $list;
  }
  
  public static function CreateCountriesSelector($id,$name,$values,$settings) {
    return CMSFormInputCountries::CreateCountriesSelector($id,$name,$values,$settings);
    
    // $settings['values'] = CMSFormInputCountries::$countries;
    // return self::CreateSelector($id,$name,$values,$settings);
  }
  
  public static function CreateSelector($id,$name,$values,$settings)  {
    
    return CMSFormInputSelect::CreateSelector($id,$name,$values,$settings);
    
    // DEPRECATED
    // if(isset($settings['expanded']) && $settings['expanded'] == true)
    //    {
    //      return self::CreateInputExpandedList($id, $name, isset($settings['values'])?$settings['values']:array(), $values, isset($settings['addtext'])?$settings['addtext']:'', isset($settings['multiple'])?true:false, $settings);
    //    }
    //    else
    //    {
    //      $items = isset($settings['values'])?$settings['values']:array();
    //      if(isset($settings['include_custom']))
    //      {
    //        $items = array('' => $settings['include_custom']) + $items;
    //      }
    //      return self::CreateInputSelectList($id, $name, $items, $values, isset($settings['size'])?$settings['size']:1, '', isset($settings['multiple'])?true:false);
    //    }
  }
  
  public static function CreateInputSelectList($id, $name, $items, $selecteditems=array(), $size=3, $addttext='', $multiple = true) {
    
    return CMSFormInputSelect::DeprecatedCreateInputSelectList($id, $name, $items, $selecteditems, $size, $addttext, $multiple);
  
  }
  
  public static function CreateInputExpandedList($id, $name, $items, $selecteditems=array(), $addttext='', $multiple = true, $params = array()) {
    
    return CMSFormInputSelect::DeprecatedCreateInputExpandedList($id, $name, $items, $selecteditems, $addttext, $multiple, $params);
      
  }
  
  public function getUploadField()  {
    return $this->getWidget()->getUploadField();
  }
  
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
    return $this->getWidget()->hasErrors();
  }

  public function noError() {
    return !$this->getWidget()->hasErrors();
  }

  public function getErrors() {
    return $this->getWidget()->getErrors();
  }

  public function showErrors()  {
    return $this->getWidget()->showErrors();
  }

  public function setError($message,$priority='default')  {
    $this->getWidget()->setError($message, $priority);
  }

}