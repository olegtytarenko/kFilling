<?php

namespace KFilling;
/**
 * Created by PhpStorm.
 * User: olegtytarenko
 * Date: 19.05.17
 * Time: 10:03
 */
abstract class Language
{
    protected $_keyBoard = [];

    protected $_keyBoardEncode = [];

    protected $_keyBoards = [];

    protected $_abc = [];

    protected $_translit = [];

    protected $loadConfig = null;


    public function __construct()
    {
        $this->autoloadAbc();
    }

    public function filling($pattern) {
        $getPossition = $this->switcher($pattern);
        $returnLetter = [];
        foreach ($getPossition as $key => $value) {
            if($value == ' ') {
                $returnLetter[$key] = $value;
            } else {
                echo var_dump($value) . PHP_EOL;
                $returnLetter[$key] = $this->getPosition(explode("|", $value), $this->_keyBoard);
                echo PHP_EOL;
            }

        }
        if($returnLetter) {
            return implode("", $returnLetter);
        }

        return $pattern;
    }

    protected function autoloadAbc() {
        if($this->loadConfig) {
            $lists = Config::get($this->loadConfig);
            if(array_key_exists('lists', $lists)) {
                $this->_abc = $lists['lists'];
            }
            if(array_key_exists('keyboards', $lists)) {
                $this->_keyBoard = $lists['keyboards'];
                foreach ($lists['keyboards'] as $key => $lists) {
                    $this->_keyBoardEncode[$key] = array_map(function($item) {
                        return array_map(function($chars) {
                            return base64_encode($chars);
                        }, $item);
                    }, $lists);
                }
            }

            if(array_key_exists('translit', $lists)) {
                $this->_translit = $lists['translit'];
            }

            $all = Config::get();
            foreach ($all as $name => $configs) {
                if(array_key_exists('keyboards', $configs) && $this->loadConfig != $name) {
                    $this->_keyBoards[$name] = [];
                    foreach ($configs['keyboards'] as $key => $lists) {
                        $this->_keyBoards[$name][$key] = array_map(function($item) {
                            return array_map(function($chars) {
                                return base64_encode($chars);
                            }, $item);
                        }, $lists);
                    }
                } else {
                    continue;
                }
            }
        }
    }

    protected function switcher($pattern) {
        $listsChars = array_map(function($item) { return base64_encode($item); }, self::str_split_utf8($pattern));
        $returnLists = [];
        foreach ($listsChars as $key => $char) {
            $returnLists[$key] = $this->getPositionKeyboard($char);
        }
        return array_map(function($item) { return $item ? $item : ' '; }, $returnLists);
    }

    private function getPosition($numbers, $lists) {
        while (($number = array_shift($numbers)) > -1) {
            if(array_key_exists($number, $lists)) {
                if(is_array($lists[$number])) {
                    return $this->getPosition($numbers, $lists[$number]);
                } else {
                    return $lists[$number];
                }
            }
        }
        return null;
    }

    /**
     * @param $strng
     * @return array
     */
    private static function str_split_utf8($strng, $isLowerCase = false)
    {
        $split = 1;
        $listsChars = [];
        for ($i = 0; $i < strlen($strng);) {
            $value = ord($strng[$i]);
            if ($value > 127) {
                if ($value >= 192 && $value <= 223) {
                    $split = 2;
                } elseif ($value >= 224 && $value <= 239) {
                    $split = 3;
                } elseif ($value >= 240 && $value <= 247) {
                    $split = 4;
                }
            } else {
                $split = 1;
            }
            $key = NULL;
            for ($j = 0; $j < $split; $j++, $i++) {
                if($isLowerCase) {
                    $key .= strtolower($strng[$i]);
                } else {
                    $key .= $strng[$i];
                }
            }
            array_push($listsChars, $key);
        }
        return $listsChars;
    }

    protected function getPositionKeyboard($charInput) {
        foreach ($this->_keyBoards as $lang => $board) {
            $findChar = array_map(function($items) use ($charInput) {
                $type = array_filter(array_map(function($filterSearch) use ($charInput) {
                    return array_search($charInput, $filterSearch);
                }, $items), function($item) {
                    return $item  !== false;
                });
                return $type;
            }, $board);
            if($findChar && is_array($findChar)) {
                $result = array_filter($findChar, function($item) { return !empty($item); });
                if(!$result) {
                    continue;
                }
                return $this->stringPositionKeyboard(
                    array_filter($findChar, function($item) { return !empty($item); })
                );
            }
        }



        // Else not found Letter
        foreach ($this->_keyBoardEncode as $lang => $board) {
            $findChar[$lang] = array_filter(array_map(function($filterSearch) use ($charInput) {
                return array_search($charInput, $filterSearch);
            }, $board), function($item) {
                return $item !== false;
            });
            if($findChar && is_array($findChar)) {
                $result = array_filter($findChar, function($item) { return !empty($item); });
                if(!$result) {
                    continue;
                }
                return $this->stringPositionKeyboard(
                    array_filter($findChar, function($item) { return !empty($item); })
                );
            }
        }

        return null;
    }

    private function stringPositionKeyboard($lists) {
        if(!$lists || !is_array($lists)) {
            return null;
        }
        $string = [];
        foreach ($lists as $key => $values) {
            $string[] = $key;
            if(is_array($values) && count($values) == 1) {
                $string[] = $this->stringPositionKeyboard($values);
            } else {
                $string[] = $values;
            }
        }

        return implode("|", $string);
    }
}