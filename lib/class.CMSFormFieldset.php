<?php

class CMSFormFieldset extends CMSForm
{
    protected $parent_form;
    protected $name;
    protected $legend;

    protected $rendered = false;

    public function __construct($form, $name, $legend = '')
    {
        parent::__construct($form->module_name, $form->id, $form->action, $form->returnid);
        $this->name = $name;
        if ($legend == '') {
            $this->legend = $name;
        } else {
            $this->legend = $legend;
        }
        $this->parent_form =& $form;
    }

    public function getLegend()
    {
        return $this->legend;
    }

    public function isShowned()
    {
        return $this->rendered;
    }

    public function showned()
    {
        $this->rendered = true;
    }

    public function render($widget_template = null, $legend = true)
    {
        $html = '';
        if (!$this->isShowned()) {
            $html .= '<fieldset';
            $html .= ($this->class) ? ' class="' . $this->class . '"' : '';
            $html .= '>';
            if ($legend) {
                $html .= '<legend>' . $this->legend . '</legend>';
            }
            $html .= $this->showWidgets($widget_template);
            $html .= '</fieldset>';
            $this->showned();
        }

        return $html;

    }

    public function setWidget($name, $type = 'text', $settings = array())
    {
        if ($type == 'file') {
            $this->parent_form->setMultipartForm();
        }
        parent::setWidget($name, $type, $settings);
    }

    public function isPosted()
    {
        return $this->parent_form->isPosted();
    }

}
