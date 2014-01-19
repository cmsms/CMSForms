<?php

class CMSFValidatorNotEmpty extends CMSFValidator
{
    public function check()
    {
        $values = $this->getWidget()->getValues();
        $value = $this->getWidget()->getValue();
        // TODO: Issue when value === 0
        if (is_array($values) && count($values) == 0)
        {
            throw new Exception($this->getErrorMessage('field_cannot_be_empty', $this->getFieldFriendlyName()));
        }
        elseif(empty($values))
        {
            throw new Exception($this->getErrorMessage('field_cannot_be_empty', $this->getFieldFriendlyName()));
        }
        elseif(empty($value))
        {
            throw new Exception($this->getErrorMessage('field_cannot_be_empty', $this->getFieldFriendlyName()));
        }
        return true;
    }
}