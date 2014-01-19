<?php

class CMSFValidatorEqualField extends CMSFValidator
{
    public function check()
    {
        $value1 = serialize($this->getWidget()->getValues());

        try {
            $value2 = serialize($this->getWidget()->getForm()->getWidget($this->params)->getValues());
        } catch (Exception $e) {
            throw new Exception($this->getErrorMessage('unknown field', $this->params));
            return false;
        }

        if ($value1 != $value2) {
            throw new Exception($this->getErrorMessage('fields not equal', $this->getWidget()->getLabel(), $this->getWidget()->getForm()->getWidget($this->params)->getLabel()));
        }

        return true;
    }
}