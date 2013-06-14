<?php

/*
  CMSForm Input Select
*/

class CMSFormInputSelect extends CMSFormInput
{
    public function getInput()
    {
        return self::CreateSelector($this->id, $this->name, $this->getValues(), $this->settings);
    }


    public static function CreateSelector($id, $name, $values, $settings = array())
    {

        if (isset($settings['expanded']) && $settings['expanded'] == true) {
            return self::CreateInputExpandedList($id, $name, isset($settings['values']) ? $settings['values'] : array(), $values, isset($settings['addtext']) ? $settings['addtext'] : '', isset($settings['multiple']) ? true : false, $settings);
        } else {
            $items = isset($settings['values']) ? $settings['values'] : array();
            if (isset($settings['include_custom'])) {
                $items = array('' => $settings['include_custom']) + $items;
            }
            return self::CreateInputSelectList($id, $name, $items, $values, isset($settings['size']) ? $settings['size'] : 1, isset($settings['addtext']) ? $settings['addtext'] : '', isset($settings['multiple']) ? true : false, $settings);
        }
    }

    protected static function CreateInputSelectList($id, $name, $items, $selecteditems = array(), $size = 3, $addttext = '', $multiple = true, $settings = array())
    {
        $id = cms_htmlentities($id);
        $name = cms_htmlentities($name);
        $size = cms_htmlentities($size);
        $multiple = cms_htmlentities($multiple);

        if ($multiple == true) {
            $name .= '[]';
        }

        $text = '<select name="' . $id . $name . '" id="' . $id . $name . '"';

        if ($addttext != '') {
            $text .= ' ' . $addttext;
        }

        if ($multiple) {
            $text .= ' multiple="multiple" ';
        }

        if ($size > 1) {
            $text .= ' size="' . $size . '"';
        }

        if (isset($settings['class'])) {
            if (is_array($settings['class'])) $settings['class'] = implode(' ', $settings['class']);
            $text .= ' class="' . $settings['class'] . '"';
        }

        if ((count($items) > 0) && (key($items) == 0) && !is_array(current($items))) {
            $text .= ' data-placeholder="' . (string)current($items) . '"';
        }

        $text .= '>';

        $count = 0;
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $text .= '<optgroup label="' . $key . '">';
                foreach ($value as $key2 => $entry) {
                    $text .= self::generateOption($key2, $entry, $selecteditems);
                    $count++;
                }
                $text .= '</optgroup>';
            } else {
                $text .= self::generateOption($key, $value, $selecteditems);
                $count++;
            }
        }
        $text .= '</select>' . "\n";

        return $text;
    }

    protected static function generateOption($key, $value, $selecteditems)
    {
        $text = '<option value="' . $key . '"';
        if (in_array($key, $selecteditems)) {
            $text .= ' ' . 'selected="selected"';
        }
        $text .= '>';
        $text .= $value;
        $text .= '</option>';

        return $text;
    }

    protected static function CreateInputExpandedList($id, $name, $items, $selecteditems = array(), $addttext = '', $multiple = true, $params = array())
    {

        $id = cms_htmlentities($id);
        $name = cms_htmlentities($name);
        $multiple = cms_htmlentities($multiple);

        $list = array();
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $list[] = array('label' => '<strong>' . $key . '</strong>', 'input' => '');

                foreach ($item as $key2 => $entry) {
                    self::generateExpandedList($list, $id, $entry, $name, $key2, $selecteditems, $multiple, $addttext);
                }

            } else {
                self::generateExpandedList($list, $id, $item, $name, $key, $selecteditems, $multiple, $addttext);
            }
        }

        if (isset($params['mode']) && ($params['mode'] == 'html')) {
            // TODO
            return 'Not implemented yet';
        } elseif (isset($params['mode']) && ($params['mode'] == 'array')) {
            return $list;
        } else {
            $html = '';
            if (count($list) > 0) {
                $html .= '<ul>';
                foreach ($list as $item) {
                    $html .= '<li>' . $item['input'] . ' ' . $item['label'] . '</li>';
                }
                $html .= '</ul>';
            }
            return $html;
        }

    }

    protected static function generateExpandedList(&$list, $id, $item, $name, $key, $selecteditems, $multiple, $addttext)
    {
        if (in_array($key, $selecteditems)) {
            $text = ' checked="checked"' . ' ' . $addttext;
        } else {
            $text = ' ' . $addttext;
        }

        if ($multiple) {
            $list[] = array(
                'label' => '<label for="' . $id . $name . '[' . $key . ']">' . $item . '</label>',
                'input' => '<input type="checkbox" name="' . $id . $name . '[' . $key . ']" id="' . $id . $name . '[' . $key . ']" value="' . $key . '"' . $text . ' />'
            );
        } else {
            $list[] = array(
                'label' => '<label for="' . $id . $name . $key . '">' . $item . '</label>',
                'input' => '<input type="radio" name="' . $id . $name . '" id="' . $id . $name . $key . '" value="' . $key . '"' . $text . ' />'
            );
        }
        return $list;
    }

    // DEPRECATED TO BE REMOVED

    public static function DeprecatedCreateInputSelectList($id, $name, $items, $selecteditems = array(), $size = 3, $addttext = '', $multiple = true)
    {
        return self::CreateInputSelectList($id, $name, $items, $selecteditems, $size, $addttext, $multiple);
    }

    public static function DeprecatedCreateInputExpandedList($id, $name, $items, $selecteditems = array(), $addttext = '', $multiple = true, $params = array())
    {
        return self::CreateInputExpandedList($id, $name, $items, $selecteditems, $addttext, $multiple, $params);
    }
}