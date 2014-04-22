<?php

/**
 * Date: 22/04/14
 * Time: 15:38
 * Author: Jean-Christophe Cuvelier <jcc@morris-chapman.com>
 */
class CMSFormPlugins
{

    public static function RegisterPlugins(Smarty_CMS &$smarty)
    {
        $smarty->register_function('form', array('CMSFormPlugins', 'Form'));
        $smarty->register_function('form_start', array('CMSFormPlugins', 'FormStart'));
        $smarty->register_function('form_row', array('CMSFormPlugins', 'FormRow'));
        $smarty->register_function('form_rows', array('CMSFormPlugins', 'FormRows'));
        $smarty->register_function('form_fieldset', array('CMSFormPlugins', 'FormFieldset'));
        $smarty->register_function('form_fieldsets', array('CMSFormPlugins', 'FormFieldsets'));
        $smarty->register_function('form_errors', array('CMSFormPlugins', 'FormErrors'));
        $smarty->register_function('form_label', array('CMSFormPlugins', 'FormLabel '));
        $smarty->register_function('form_widget', array('CMSFormPlugins', 'FormWidget'));
        $smarty->register_function('form_end', array('CMSFormPlugins', 'FormEnd'));
    }

    private static function renderFile($filename, Smarty_Internal_Template &$smarty, $parent = null)
    {
        $file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $filename;
        if (is_file($file)) {
            return $smarty->fetch('file:' . $file, null, null, $parent);
        } else {
            throw new SmartyException('Unable to load the file ' . $file);
        }
    }

    /**
     * @param $params
     * @return CMSForm
     * @throws SmartyException
     */
    private static function getForm($params)
    {
        if (!isset($params['form']) || (!is_a($params['form'], 'CMSForm'))) {
            throw new SmartyException('parameter form must be defined and being a CMSForm object');
        } else {
            return $params['form'];
        }
    }

    /**
     * @param $params
     * @return CMSFormFieldset
     * @throws SmartyException
     */
    private static function getFieldset($params)
    {
        if (!isset($params['fieldset']) || (!is_a($params['fieldset'], 'CMSFormFieldset'))) {
            throw new SmartyException('parameter form must be defined and being a CMSFormFieldset object');
        } else {
            return $params['fieldset'];
        }
    }

    /**
     * @param $params
     * @return CMSFormWidget
     * @throws SmartyException
     */
    private static function getWidget($params)
    {
        if (!isset($params['widget'])) {
            throw new SmartyException('parameter widget must be defined');
        } elseif (!is_object($params['widget'])) {
            throw new SmartyException('parameter widget must be an object');
        } elseif (!is_a($params['widget'], 'CMSFormWidget')) {
            throw new SmartyException('parameter widget must be a CMSFormWidget object, ' . get_class($params['widget']) . ' is given');
        } else {
            return $params['widget'];
        }
    }

    public static function NotImplemented($params, Smarty_Internal_Template &$smarty)
    {
        return 'Not implemented';
    }

    public static function Form($params, Smarty_Internal_Template &$smarty)
    {
        $local_smarty = new Smarty();
        $local_smarty->assign('form', self::getForm($params));

        return self::renderFile('form.form.tpl', $smarty, $local_smarty);
    }

    public static function FormStart($params, Smarty_Internal_Template &$smarty)
    {
        $form = self::getForm($params);

        return $form->getHeaders();
    }

    public static function FormEnd($params, Smarty_Internal_Template &$smarty)
    {
        $form = self::getForm($params);

        return
                self::FormRows(array('form' => $form), $smarty)
            .   self::FormFieldsets(array('form' => $form), $smarty)
            .   $form->getButtons()
            .   $form->getFooters()
        ;
    }

    public static function FormErrors($params, Smarty_Internal_Template &$smarty)
    {
        if (isset($params['form'])) {
            $entity = self::getForm($params);
        } elseif (isset($params['widget'])) {
            $entity = self::getWidget($params);
        } else {
            throw new SmartyException('form or widget parameter undefined');
        }

        if ($entity->hasErrors()) {
            $local_smarty = new Smarty();
            $local_smarty->assign('errors', $entity->getErrors());
            $local_smarty->assign('show_priority', $entity->getShowPriority());

            return self::renderFile('form.errors.tpl', $smarty, $local_smarty);
        }
        return null;
    }

    public static function FormFieldsets($params, Smarty_Internal_Template &$smarty)
    {
        $form = self::getForm($params);
        $result = '';
        foreach($form->getFieldsets() as $fieldset)
        {
            if(!$fieldset->isShowned())
            {
                $result .= self::FormFieldset(array('fieldset' => $fieldset), $smarty);
            }
        }
        return $result;
    }

    public static function FormFieldset($params, Smarty_Internal_Template &$smarty)
    {
        $fieldset = self::getFieldset($params);
        if(!$fieldset->isShowned())
        {
            $local_smarty = new Smarty();
            $local_smarty->assign('fieldset', $fieldset);
            $fieldset->showned();
            return self::renderFile('form.fieldset.tpl', $smarty, $local_smarty);
        }
        return null;
    }

    public static function FormRows($params, Smarty_Internal_Template &$smarty)
    {
        $form = self::getForm($params);

        $result = '';

        foreach($form->getAllWidgets() as $widget)
        {
            if(!$widget->isShowned())
            {
                $result .= self::FormRow(array('widget' => $widget), $smarty);
            }
        }

        return $result;
    }

    public static function FormRow($params, Smarty_Internal_Template &$smarty)
    {
        $widget = self::getWidget($params);
        if (!$widget->isShowned()) {
            $local_smarty = new Smarty();
            $local_smarty->assign('widget', $widget);

            return self::renderFile('form.fields.tpl', $smarty, $local_smarty);
        }

        return null;
    }

    public static function FormWidget($params, Smarty_Internal_Template &$smarty)
    {
        $widget = self::getWidget($params);
        if (!$widget->isShowned()) {
            $local_smarty = new Smarty();
            $local_smarty->assign('input', $widget->getInput());
            if ($tips = $widget->getTips()) {
                $local_smarty->assign('tips', $tips);
            }
            $widget->showned();

            return self::renderFile('form.widget.tpl', $smarty, $local_smarty);
        }

        return null;
    }

    public static function FormLabel($params, Smarty_Internal_Template &$smarty)
    {
        $widget = self::getWidget($params);

        return $widget->getLabelTag();
    }
} 