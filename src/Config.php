<?php
/**
 * Created by PhpStorm.
 * User: olegtytarenko
 * Date: 19.05.17
 * Time: 10:25
 */

namespace KFilling;


use Symfony\Component\Yaml\Yaml;

class Config
{
    private $listsConfig = [];

    private static $instance;

    private function __construct()
    {
        $pathConfig = realpath(__DIR__.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'config');
        if($pathConfig && is_dir($pathConfig)) {
            $listsFiles = array_filter(scandir($pathConfig), function($fileName) {
                return preg_match('/\.yml$/', $fileName);
            });

            $this->mapParse($listsFiles, $pathConfig);

        }
    }

    private function mapParse($listsFiles, $pathConfig) {
        if($listsFiles) {
            foreach ($listsFiles as $fileName) {
                $name = preg_replace('/\.yml$/', null, $fileName);
                $this->listsConfig[$name] = Yaml::parse(file_get_contents($pathConfig.DIRECTORY_SEPARATOR.$fileName));
            }
        }
    }

    private function getResource($pattern) {
        if(array_key_exists($pattern, $this->listsConfig)) {
            return $this->listsConfig[$pattern];
        } else {
            return $this->listsConfig;
        }
    }



    public static function get($patternSearch = null) {
        if(!self::$instance instanceof Config) {
            self::$instance = new self();
        }
        return self::$instance->getResource($patternSearch);
    }


}