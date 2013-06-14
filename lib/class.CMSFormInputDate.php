<?php

  /*
    CMSForm Input Date
  */
  
  class CMSFormInputDate extends CMSFormInputSelect
  {
    
    public function getValue()  {
      if ((count($this->values) > 1))
      {
        return $this->values['0'] . '-' . $this->values['1'] . '-' . $this->values['2'];
      }
      else
      {
        return implode('-', $this->values);
      }
    }
    
    public function getInput() {
      return self::CreateDateSelect($this->id,$this->name,$this->getValues(), $this->settings);
    }
    
    public static function CreateNumberList($end, $start=0) {
      if (($end < 0)||(!is_numeric($end))) $end = 1;
      $list = array();
      for ($i = $start; $i <= $end; $i++)
      {
        $list[$i] = ($i < 10)? '0' . (string)$i:(string)$i;
      }
      return $list;
    }
    
    protected static function createDateSelect($id,$name,$values,$settings)  {
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
    
    protected static function CreateMonthsList() {
      $months = array();
      for ($i = 1; $i <= 12; ++$i) {
        $t = mktime(0, 0, 0, $i, 1, 2000);
        $months[$i] = date('M', $t);
      }
      return $months;
    }
  
  }