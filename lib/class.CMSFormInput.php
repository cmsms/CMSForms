<?php

/*
  CMSForm Input base
*/

/**
 * @property mixed show_priority
 */
class CMSFormInput
{
    protected $id;
    protected $name;
    protected $values = array(); // We always store results in an array. In case of single field, the single value is array[0]

    protected $form;
    protected $module_name;
    protected $settings = array();

    // VALIDATION
    protected $is_valid = true;
    protected $form_errors;

    // PROCESSING
    protected $showned = false;
    protected $hide = false;

    protected $template = '<div class="form_widget">
        <div class="form_label"><label for="%ID%">%LABEL%</label></div>
        <div class="form_errors">%ERRORS%</div>
        <div class="form_input">%INPUT% <em>%TIPS%</em></div>
      </div>';

    protected $admin_template = '<div class="pageoverflow">
		<div class="pagetext">%LABEL%:</div>
		<div class="pageinput">%INPUT% <em>%TIPS%</em></div>
		<div class="pageinput" style="color: red;">%ERRORS%</div>
	</div>';

    public function __construct()
    {
        return $this;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
            return null;
        }
    }

    public function setup($id, $name, &$form, $module_name, $settings = array())
    {
        $this->id = $id;
        $this->name = isset($settings['name']) ? $settings['name'] : $name;
        $this->form = $form;
        $this->module_name = $module_name;
        $this->settings = $settings;

        return $this;
    }

    public function init()
    {
        // Initialize the field
        $this->initValues();

        return $this;
    }

    public function getForm()
    {
        if (!is_object($this->form)) {
            throw new Exception('An error occured retrieving the form object.');
        }
        return $this->form;
    }

    public function getModule()
    {
        if ($module = cms_utils::get_module($this->module_name)) {
            return $module;
        } else {
            return cms_utils::get_module('CMSForms'); // Default safeback
        }
    }

    public function getSetting($setting, $default_value = null)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : $default_value;
    }

    public function setSetting($setting, $value)
    {
        $this->settings[$setting] = $value;
    }

    // ##### FORM #####

    public function getInput()
    {
        return (string)$this->getValue(); // Don't know what to do? Show the value.
    }

    // ##### WIDGET PROCESSING #####

    public function process($save = true)
    {
        // Validate
        $this->validate();

        // Save values
        if ($save == true) {
            $this->save();
        }
    }

    public function show($template = null, $force = false)
    {
        // TODO: REFACTORING

        if ((!$this->showned || $force)) {
            $html = '';

            // if (!is_null($template) && ($this->type != 'hidden')) // FIXME
            if (!is_null($template)) {
                $text = str_replace('%ID%', $this->id . $this->name, $template);
                $text = str_replace('%FIELDNAME%', $this->name, $text);
                $text = str_replace('%LABEL%', $this->getLabel(), $text);
                $text = str_replace('%LABEL_TAG%', $this->getLabelTag(), $text);
                $text = str_replace('%INPUT%', $this->getInput(), $text);
                $text = str_replace('%ERRORS%', $this->showErrors(), $text);
                $text = str_replace('%TIPS%', $this->getTips(), $text);
                $text = str_replace('%PREFIX%', $this->getTips(), $text);
                $text = str_replace('%SUFIX%', $this->getTips(), $text);
                $html .= $text;
            } else {
                $html .= (string)$this;
            }

            if (isset($this->settings['with_div'])) {
                $html = '<div class="' . $this->getSetting('class', 'field_' . $this->name) . '">' . $html . '</div>';
            }

            $this->showned = true;
            return $html;
        }
        return null;
    }

    // ##### COSMETICS #####

    public function __toString()
    {
        return $this->show($this->getTemplate());
    }

    public function getTemplate()
    {
        global $CMS_ADMIN_PAGE;

        if($CMS_ADMIN_PAGE)
        {
            return $this->admin_template;
        }
        else
        {
            return $this->template;
        }
    }

    public function getLabel()
    {
        if (isset($this->settings['label'])) {
            return $this->settings['label'];
        }
        // Try to get it from language file
        return $this->getModule()->lang('form_' . $this->name);
    }

    public function getFriendlyName()
    {
        // DEPRECATED (USED IN VALIDATOR) (USED in CMSFormWidget)
        $this->getLabel();
    }

    public function getLabelTag()
    {
        return '<label for="' . $this->id . $this->name . '">' . $this->getLabel() . '</label>';
    }

    public function getTips()
    {
        if (isset($this->settings['tips'])) {
            return $this->settings['tips'];
        }
        // Try to get it from language file

        // if (cms_utils::get_module($this->module_name))
        //   {
        //     // TODO: This should be shown only it the lang key exists...
        //     return cms_utils::get_module($this->module_name)->lang('tips_'.$this->name);
        //   }
        return null;
    }


    // ##### VALUES #####

    // SAVE

    public function save()
    {
        if ($this->isValid() == true) {
            if (isset($this->settings['object'])) {
                $this->saveObject();
            }
            if (isset($this->settings['preference'])) {
                $this->savePreference();
            }
            if (isset($this->settings['user_preference'])) {
                $this->saveUserPreference();
            }
        }
    }

    protected function saveObject()
    {

        // This do not save the object state, so we have to do it outside the form

        $values = $this->getValue();

        if (isset($this->settings['set_method'])) {
            $this->settings['object']->{$this->settings['set_method']}($values);
        } else {
            if (isset($this->settings['field_name'])) {
                $name = $this->settings['field_name'];
            } else {
                $name = $this->name;
            }

            if (method_exists($this->settings['object'], 'set')) {
                $this->settings['object']->set($name, $values);
            } else {
                try {
                    $this->settings['object']->$name = $values;
                } catch (Exception $e) {
                    die('Unable to save the object: ' . $e->getMessage());
                }
            }
        }
    }

    protected function savePreference()
    {
        if (isset($this->settings['preference']) && !isset($_REQUEST[$this->id . 'cancel'])) {
            // Check if there is no cancel button first because we save the value directly !
            $this->getModule()->setPreference($this->settings['preference'], $this->getValue());
        }
    }

    protected function saveUserPreference()
    {
        $_SESSION['modules'][$this->module_name]['preferences'][$this->settings['user_preference']] = $this->getValue();
    }

    // Values manipulation

    public function initValues()
    {
        if (isset($_REQUEST[$this->id . $this->name])) {
            if (is_array($_REQUEST[$this->id . $this->name])) {
                $this->setValues($_REQUEST[$this->id . $this->name]);
            } else {
                $this->setValues(html_entity_decode($_REQUEST[$this->id . $this->name]));
            }
        } elseif (isset($this->settings['value'])) {
            $this->setValues($this->settings['value']);
        } elseif (isset($this->settings['object']) && !$this->getForm()->isPosted()) {
            $this->setValues($this->fetchObjectValues());
        } elseif (isset($this->settings['preference']) && $this->getModule()->getPreference($this->settings['preference']) != '') {
            $this->setValues($this->getModule()->getPreference($this->settings['preference']));
        } elseif (isset($this->settings['user_preference']) && ($value = $this->getUserPreference())) {
            $this->setValues($value);
        }

        if (isset($this->settings['default_value']) && !$this->getForm()->isPosted()) {
            if ($this->isEmpty()) {
                $this->setValues($this->settings['default_value']);
            }
        }
    }

    protected function fetchObjectValues()
    {

        if (isset($this->settings['object']) && is_object($this->settings['object'])) {
            if (isset($this->settings['get_method'])) {
                return $this->settings['object']->{$this->settings['get_method']}();
            } else {
                $name = isset($this->settings['field_name']) ? $this->settings['field_name'] : $this->name;

                if (method_exists($this->settings['object'], 'get')) {
                    return $this->settings['object']->get($name);
                } else {
                    try {
                        return $this->settings['object']->$name;
                    } catch (Exception $e) {
                        audit('', 'CMSForms', 'Unable to retrieve value for field ' . $name . ' with message ' .  $e->getMessage());
                    }
                }
            }
        }

        return null;
    }

    protected function getUserPreference()
    {
        if (isset($_SESSION['modules'][$this->module_name]['preferences'][$this->settings['user_preference']])) {
            return $_SESSION['modules'][$this->module_name]['preferences'][$this->settings['user_preference']];
        }
        return null;
    }

    public function resetValues()
    {
        $this->values = array();
    }

    public function getValue()
    {
        if (count($this->values) == 1) {
            reset($this->values);
            return (string)current($this->values);
        } else {
            return (string)implode('|||', $this->values);
        }
    }

    public function setValue($value, $key = 0)
    {
        $this->values[$key] = (string)$value;
    }

    public function removeValueIfEqual($value)
    {
        if ($this->getValue() == $value) {
            $this->setValues(array());
        }
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values = array())
    {
        if (is_array($values)) {
            $this->values = $values;
        } elseif (strpos($values, '|||') !== false) {
            $this->values = explode('|||', $values);
        } else {
            $this->values[0] = $values;
        }
    }

    public function unsetValues()
    {
        $this->values = array();
    }

    public function setDefaultValues($values)
    {
        if (is_array($values)) {
            $this->values = $values;
        } else {
            $this->values = array($values);
        }
    }

    public function isEmpty()
    {
        if ((count($this->values) == 1) && (empty($this->values[0])) || (count($this->values) == 0)) {
            return true;
        } else {
            return false;
        }
    }

    // Values validation

    protected function validate()
    {
        if (isset($this->settings['validators']) && is_array($this->settings['validators'])) {
            // CHANGE THAT

            foreach ($this->settings['validators'] as $validator => $value) {
                $validate = new CMSFormValidator($this, $validator, $value);
                try {
                    if ($validate->check() === false) $this->is_valid = false;
                } catch (Exception $e) {
                    $this->setError($e->getMessage(), 'form error');
                }

            }
        }
    }

    public function isValid()
    {
        if (($this->is_valid == false) || $this->hasErrors()) {
            return false;
        }
        return true;
    }

    public function setValidator($validator, $params = array())
    {
        return $this->addValidator($validator, $params);
    }

    public function addValidator($validator, $params = array())
    {
        return $this->settings['validators'][$validator] = $params;
    }

    public function removeValidator($validator)
    {
        unset($this->settings['validators'][$validator]);
    }

    // ERRORS

    public function hasErrors()
    {
        if (count($this->form_errors) == 0) {
            return false;
        }
        return true;
    }

    public function noError()
    {
        return !$this->hasErrors();
    }

    public function getErrors()
    {
        return $this->form_errors;
    }

    public function showErrors()
    {
        $html = '';
        if (count($this->form_errors) > 0) {
            $html .= '<ul class="form_widget_errors">';
            foreach ($this->form_errors as $priority => $errors) {
                $html .= '<li>';
                if ($this->show_priority) $html .= '<em class="form_widget_error_priority">' . $priority . '</em>';
                $html .= '<ul>';
                foreach ($errors as $error) {
                    $html .= '<li class="form_widget_error_message">' . $error . '</li>';
                }
                $html .= '</ul></li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    public function setError($message, $priority = 'default')
    {
        $this->form_errors[$priority][] = $message;
    }


}