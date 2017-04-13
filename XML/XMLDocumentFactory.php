<?php

namespace KunicMarko\StaticTranslationsBundle\XML;

class XMLDocumentFactory
{
    /**
     * Build XMLDocument
     * @param string $sourceLanguage
     * @param string $directory
     * @param string $currentLanguage
     * @return XMLDocument
     */
    public static function build(string $sourceLanguage, string $directory, string $currentLanguage)
    {
        $xml = new XMLDocument($directory, $currentLanguage);

        if (file_exists($xml->getFileName())) {
            $xml->load();
        } else {
            self::buildNewXMLDocument($xml, $sourceLanguage, $directory);
        }

        return $xml;
    }

    /**
     * Add needed elements for new XML file
     * @param XMLDocument $xml
     * @param string $sourceLanguage
     * @param string $directory
     */
    private static function buildNewXMLDocument(XMLDocument $xml, string $sourceLanguage, string $directory)
    {
        $body = $xml->createElement('body');

        $xliff = $xml->createElement('xliff', [
            'xmlns' => 'urn:oasis:names:tc:xliff:document:1.2',
            'version' => '1.2'
        ]);

        $file = $xml->createElement('file', [
            'source-language' => $sourceLanguage,
            'datatype' => 'plaintext',
            'original' => 'file.ext'
        ]);

        $file->appendChild($body);
        $xliff->appendChild($file);
        $xml->appendChild($xliff);

        if (!file_exists($directory)) {
            exec('mkdir '.$directory);
        }
    }
}
