<?php

namespace KunicMarko\StaticTranslationsBundle\Services;

class HelperService
{
    private $xml;
    
    //give us Excel Column letter for a number
    public function num2alpha($n)
    {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n%26 + 0x41) . $r;
        }
        return $r;
    }
    //create XML element
    public function createElement($name, $attributes = null)
    {
        $element = $this->xml->createElement($name);
        
        if ($attributes === null || empty($attributes)) {
            return $element;
        }
        
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }
        return $element;
    }
    //Check if id attribute already exists
    public function checkIfIdExists($name)
    {
        $name = str_replace(' ', '_', $name);
        $translations = $this->xml->getElementsByTagName('trans-unit');
        
        foreach ($translations as $translation) {
            if ($translation->getAttribute('id') == $name) {
                return true;
            }
        }
        return false;
    }
     //create trans-unit
    public function createTranslationElement($target, $source)
    {
        $trans = $this->createElement('trans-unit', [
            'id' => str_replace(' ', '_', $source)
        ]);

        $trans->appendChild($this->createTextElement('source', $source));
        $trans->appendChild($this->createTextElement('target', $target));
        return $trans;
    }
    // create trans-unit body
    private function createTextElement($el, $text)
    {
        $element = $this->createElement($el);
        $elementText = $this->xml->createTextNode($text);
        $element->appendChild($elementText);
        return $element;
    }
    //Help text for command
    public function getHelpText()
    {
        return <<<EOT
               
Excel Formating : 
                    
--- ---------- ----------- -------------------------------------
     A          B
--- ---------- ----------- -------------------------------------
 1   English    German
 2
 3   About Us   Über uns    label.about
 4   Contact    Kontakt
 5   Imprint    Impressum   form.about, default.language.source
--- ---------- ----------- -------------------------------------
                    
We expect words for translation to start from line 3
You can add more languages, we only expect labels to be at last position
Labels are optional, there can be more than one label for same word, 
they just have to be devided by commma (,)
If you add labels, label names will be used for source translation tags in xml
if you want to use default language word for source and use lables for same word, 
you can use reserved word "default.language.source" and add it in labels part
                    
Excel file has to end with .xlsx
                    
We expect array of language codes, divided by space e.g. ( en de fr ), use same order as in your excel file

First language in array is source language and will be used for all source tags                   
EOT;
    }
    
    public function setXml($xml)
    {
        $this->xml = $xml;
    }
}
