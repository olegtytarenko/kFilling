<?php

namespace KFilling;
use KFilling\Languages\English;
use KFilling\Languages\Russian;
use KFilling\Languages\Ukranian;

/**
 * Created by PhpStorm.
 * User: olegtytarenko
 * Date: 19.05.17
 * Time: 10:02
 */
class KFilling
{
    public static function getValidString($pattern, $locale = 'ru') {
        $language = null;
        switch ($locale) {
            case 'uk':
                $language = new Ukranian();
                break;
            case 'ru':
                $language = new Russian();
                break;
            case 'en':
                $language = new English();
                break;
        }

        if(!$language) {
            return $pattern;
        }

        return $language->filling($pattern);
    }
}