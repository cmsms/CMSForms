<?php

  /*
    CMSForm Input DateTime
		Based on a timestamp value
  */
  
  class CMSFormInputDateTime extends CMSFormInputTime
  {
    
    public function getValue()  {
      if ((count($this->values) > 1))
      {
				return strtotime(
					$this->values['0'] . '-' . $this->values['1'] . '-' . $this->values['2']
					. ' ' . 
					$this->values['3'] . ':' . $this->values['4'] . ':' . $this->values['5']
				);

      }
      else
      {
        return implode('-', $this->values);
      }
    }

		public function getTime()
		{
			return date('H:i:s', $this->getValue());
		}
		
		public function getDate()
		{
			return date('Y-m-d', $this->getValue());			
		}
    
    public function getInput() {	
      return
 				self::CreateDateSelect($this->id,$this->name,explode('-', $this->getDate()), $this->settings)
 				. self::CreateTimeSelect($this->id,$this->name,explode(':',$this->getTime()), 3);
    } 
  }