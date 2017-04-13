<?php

namespace KunicMarko\StaticTranslationsBundle\XML;

use \DOMDocument;
use \DomNode;

class XMLDocument
{
    const FILE_NAME  = 'messages.%s.xliff';
    const TRANSLATION_TAG_NAME = 'trans-unit';

    /** @var DOMDocument */
    private $xml;
    /** @var string */
    private $fileName;

    public function __construct(string $directory, string $language)
    {
        $this->xml = new \DOMDocument('1.0', 'utf-8');
        $this->xml->preserveWhiteSpace = false;
        $this->xml->formatOutput = true;
        $this->setFileName($directory, $language);
    }

    /**
     * @param string $directory
     * @param string $language
     */
    public function setFileName(string $directory, string $language)
    {
        $this->fileName = $directory.sprintf(self::FILE_NAME, $language);
    }

    /**
     * Load XML from a file
     */
    public function load()
    {
        $this->xml->load($this->fileName);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Adds new child at the end of the children
     * @param DomNode $child
     */
    public function appendChild(DOMNode $child)
    {
        $this->xml->appendChild($child);
    }

    /**
     * Get body tag from XML file
     * @return mixed
     */
    public function getBody()
    {
        return $this->xml->getElementsByTagName('body')[0];
    }


    /**
     * Save xml file
     */
    public function save()
    {
        $this->xml->save($this->fileName);
    }

    /**
     * Check if element with given id is present
     * @param string $name
     * @return bool
     */
    public function isElementPresent(string $name)
    {
        $name = str_replace(' ', '_', $name);
        $translations = $this->xml->getElementsByTagName(self::TRANSLATION_TAG_NAME);
        
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
        $element = $this->xml->createElement($name);
        
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
        $elementText = $this->xml->createTextNode($text);
        $element->appendChild($elementText);
        return $element;
    }
}
