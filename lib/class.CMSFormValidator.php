<?php

/*
  This class aim to handle CMS Forms very differently.

  Author: Jean-Christophe Cuvelier <cybertotophe@gmail.com>
  Copyrights: Jean-Christophe Cuvelier 2012
  Licence: GPL

*/

/**
 * Class CMSFormValidator
 * @deprecated use CMSFValidator sub-classes
 */

class CMSFormValidator
{

    private $validator;

    public function __construct(&$widget, $validator_name, $params)
    {
        $this->validator = $this->buildValidator($validator_name, $widget, $params);

        return $this;
    }

    private function buildValidator($validator_name, &$widget, $params)
    {
        switch($validator_name)
        {
            case 'not_empty':
                $validator = new CMSFValidatorNotEmpty($params);
                break;
            case 'equal_field':
                $validator = new CMSFValidatorEqualField($params);
                break;
            case 'email':
                $validator = new CMSFValidatorEmail($params);
                break;
            case 'unique':
                $validator = new CMSFValidatorUnique($params);
                break;
            default;
                $validator = new CMSFValidator($params);
                break;
        }

        $validator->setWidget($widget);
        return $validator;
    }

    private function getValidator()
    {
        return $this->validator;
    }

    public function check()
    {
        return $this->getValidator()->check();
    }

    /**
     * @param $email
     * @return bool
     * @deprecated use CMSFValidatorEmail::validEmail
     */
    public static function validEmail($email)
    {
        return CMSFValidatorEmail::validEmail($email);
    }
}