<?php

class CMSFValidatorUnique extends CMSFValidator
{
    public function check()
    {
        $value = $this->getWidget()->getValue();
        if (call_user_func($this->params, $value) !== null)
        {
            throw new Exception($this->getErrorMessage('field not unique', $value));
        }
        return true;
    }
}