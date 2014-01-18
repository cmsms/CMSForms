<?php

  /*
    CMSForm Input Pages
  */
  
  class CMSFormInputPages extends CMSFormInputSelect
  {
    public function getInput()
    {
      $this->settings['values'] = array(0 => '&laquo; ' . cms_utils::get_module('CMSForms')->lang('select one') . ' &raquo;') + self::getPagesList($this->id, $this->name,$this->getValue(), $this->settings);
      return self::CreateSelector($this->id, $this->name, $this->getValues(), $this->settings);
    }
    
    protected static function getPagesList($id,$name,$value,$settings = array()) {

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

    protected static function getPageId($alias)  {
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

    protected static function createPageSelect($id,$name,$value,$settings = array()) {
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
    
  }