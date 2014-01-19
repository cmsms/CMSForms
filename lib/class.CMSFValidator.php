<?php

class CMSFValidator
{
    /** @var CMSFormWidget $widget */
    protected $widget;

    protected $params;

    public function __construct($params = array())
    {
        $this->params = $params;

        return $this;
    }

    public function setWidget(&$widget)
    {
        $this->widget = $widget;
    }

    protected function getWidget()
    {
        if (!empty($this->widget)) {
            return $this->widget;
        } else {
            throw new Exception('Widget not defined for the validator');
        }
    }

    public function hasWidget()
    {
        return (bool)(!empty($this->widget));
    }

    public function check()
    {
        return true; // This validator don't validate anything
    }

    protected function getFieldFriendlyName()
    {
        if(isset($this->params['field_name']))
        {
            return $this->params['field_name'];
        }
        else
        {
            return $this->getWidget()->getFriendlyName();
        }
    }

    protected function getErrorMessage($message, $value)
    {
        if (is_array($this->params) && isset($this->params['error_message']) && $this->params['error_message'] != '') {
            return $this->params['error_message'];
        } else {
            if($i18n = cms_utils::get_module('I18n'))
            {
                $template =  cms_utils::get_module('CMSForms')->lang($message);
                $translation = I18n::__($template);
                $error = @vsprintf($translation, $value);
            }
            else
            {
                $error = cms_utils::get_module('CMSForms')->lang($message, $value);
            }
            return $error;
        }
    }
}