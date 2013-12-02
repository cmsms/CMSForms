<?php

/*
  CMSForm Input Checkbox
*/

class CMSFormInputCheckbox extends CMSFormInput
{

    protected $template = '<div class="form_widget">
        <div class="form_label"><label for="%ID%">%LABEL%</label></div>
        <div class="form_errors">%ERRORS%</div>
        <div class="form_input">%INPUT% <em>%TIPS%</em></div>
      </div>';

    protected $admin_template = '<div class="pageoverflow">
		<div class="pagetext">%LABEL%:</div>
		<div class="pageinput"><label>%INPUT% <em>%TIPS%</em></label></div>
		<div class="pageinput" style="color: red;">%ERRORS%</div>
	</div>';

    public function initValues()
    {
        // var_dump('init checkbox ' . $this->name);
        // var_dump($this->getValues());
        if (
            (!isset($_REQUEST[$this->id . $this->name]) || is_null($_REQUEST[$this->id . $this->name]))
            &&
            (
                isset($_REQUEST[$this->id . 'submit'])
                ||
                isset($_REQUEST[$this->id . 'apply'])
            )

        ) {
            // Case when checkbox is unchecked and form is submitted, we should empty the value
            $this->setValues();
            // $this->setValue(-1);
        } else {
            parent::initValues();
        }
        // var_dump($this->getValues());
        // var_dump($this->getValues());
    }

    public function getInput()
    {
        $input = $this->getModule()->CreateInputCheckbox($this->id, $this->name, '1', (integer)(boolean)$this->getValue());

        if($text = $this->getSetting('text'))
        {
            $input .= ' ' . $text;
        }

        return $input;
    }
}