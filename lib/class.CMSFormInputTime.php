<?php

  /*
    CMSForm Input Time
  */
  
  class CMSFormInputTime extends CMSFormInputDate
  {
    
    public function getValue()  {
      if ((count($this->values) > 1))
      {
        return $this->values['0'] . ':' . $this->values['1'] . ':' . $this->values['2'];
      }
      else
      {
        return implode('|||', $this->values);
      }
    }
    
    public function getInput() {
      return self::CreateTimeSelect($this->id,$this->name,$this->getValues());
    }
    
    protected static function CreateTimeSelect($id,$name,$values)  {
      if (count($values) == 1)
      {
        if (strpos($values[0], ':') !== false) $values = explode(':',$values[0]);
      }
      $hours = self::CreateInputSelectList($id,$name.'[0]',self::CreateNumberList(23),array($values[0]),1,'',false);
      $minutes = self::CreateInputSelectList($id,$name.'[1]',self::CreateNumberList(59),array($values[1]),1,'',false);
      $seconds = self::CreateInputSelectList($id,$name.'[2]',self::CreateNumberList(59),array($values[2]),1,'',false);
      return $hours . ' : ' . $minutes . ' : ' . $seconds;
    }
  }