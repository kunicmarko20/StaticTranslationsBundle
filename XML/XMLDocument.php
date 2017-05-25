<?php

namespace KunicMarko\StaticTranslationsBundle\XML;

class XMLDocument extends \DOMDocument
{
    const FILE_NAME_PATTERN  = 'messages.%s.xliff';
    const TRANSLATION_TAG_NAME = 'trans-unit';

    /** @var string */
    private $fileName;

    public function __construct($directory, $language)
    {
        parent::__construct('1.0', 'utf-8');
        $this->preserveWhiteSpace = false;
        $this->formatOutput = true;
        $this->setFileName($directory, $language);
    }

    /**
     * @param string $directory
     * @param string $language
     */
    private function setFileName($directory, $language)
    {
        $this->fileName = $directory.sprintf(self::FILE_NAME_PATTERN, $language);
    }

    /**
     * Load existing XML file
     */
    public function importFile()
    {
        parent::load($this->fileName);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Get body tag from XML file
     * @return mixed
     */
    public function getBody()
    {
        return $this->getElementsByTagName('body')[0];
    }


    /**
     * Save xml file
     */
    public function exportToFile()
    {
        parent::save($this->fileName);
    }

    /**
     * Check if element with given id is present
     * @param string $name
     * @return bool
     */
    public function isElementPresent($name)
    {
        $name = str_replace(' ', '_', $name);
        $translations = $this->getElementsByTagName(self::TRANSLATION_TAG_NAME);
        
        foreach ($translations as $translation) {
            if ($translation->getAttribute('id') == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create XML element with attributes
     * @param string $name
     * @param array|null $attributes
     * @return \DOMElement
     */
    public function createElement($name, $attributes = null)
    {
        $element = parent::createElement($name);
        
        if ($attributes === null || empty($attributes)) {
            return $element;
        }
        
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }
        return $element;
    }

    /**
     * Create XML translation element
     * @param string $target
     * @param string $source
     * @return \DOMElement
     */
    public function createTranslationElement($target, $source)
    {
        $trans = $this->createElement(self::TRANSLATION_TAG_NAME, [
            'id' => str_replace(' ', '_', $source)
        ]);

        $trans->appendChild($this->createTextElement('source', $source));
        $trans->appendChild($this->createTextElement('target', $target));
        return $trans;
    }

    /**
     * Create XML text element for translation element
     * @param string $elementName
     * @param string $text
     * @return \DOMElement
     */
    private function createTextElement($elementName, $text)
    {
        $element = $this->createElement($elementName);
        $elementText = $this->createTextNode($text);
        $element->appendChild($elementText);
        return $element;
    }
}
