<?php
namespace App\Util;
class Slugger
{
    private $isLower;

    public function __construct($toLower){

        $this->isLower = $toLower;

    }

    public function sluggify(string $strToConvert){

        if($this->isLower)
        {
            $strToConvert = strtolower($strToConvert);
        }

        $convertedString = preg_replace( 
            '/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', 
            '-', 
            trim(
                strip_tags($strToConvert)
            )
        );

        return $convertedString;
    }
}