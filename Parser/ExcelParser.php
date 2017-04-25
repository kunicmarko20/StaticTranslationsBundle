<?php

namespace KunicMarko\StaticTranslationsBundle\Parser;

use KunicMarko\StaticTranslationsBundle\Adapter\ExcelReaderAdapterInterface;
use KunicMarko\StaticTranslationsBundle\XML\XMLDocument;

class ExcelParser implements ParserStrategyInterface
{
    const DEFAULT_LANGUAGE_LABEL = 'default.language.source';
    const SOURCE_COLUMN_NAME = "A";

    /** @var ExcelReaderAdapterInterface */
    private $excelReader;

    public function __construct(ExcelReaderAdapterInterface $excelReader)
    {
        $this->excelReader = $excelReader;
    }

    /**
     * {@inheritDoc}
     */
    public function parse(XMLDocument $xml, $currentLanguageColumn, $labelColumn)
    {
        $lastRow = $this->excelReader->getLastRow();
        $body = $xml->getBody();

        // Words start from third row in excel file
        for ($row = 3; $row <= $lastRow; $row++) {
            $target = $this->excelReader->getCell($currentLanguageColumn.$row);
            $source = $this->excelReader->getCell(self::SOURCE_COLUMN_NAME.$row);
            
            if (empty($target) && empty($source)) {
                break;
            }

            if (!$this->handleLabels($xml, $row, $target, $labelColumn)) {
                continue;
            }
            
            $source = $currentLanguageColumn != self::SOURCE_COLUMN_NAME ? $source : $target;
            if ($xml->isElementPresent($source)) {
                continue;
            }

            $body->appendChild($xml->createTranslationElement($target, $source));
        }
    }
    /**
     * If there are labels for translation instead of main word, add translation for labels
     * @param XMLDocument $xml
     * @param integer $row
     * @param string $target
     * @param string $labelColumn
     * @return bool
     */
    private function handleLabels(XMLDocument $xml, $row, $target, $labelColumn)
    {
        $labels = $this->excelReader->getCell($labelColumn.$row);
        if (empty($labels)) {
            return true;
        }
        $labels = explode(',', $labels);
        $body = $xml->getBody();

        foreach ($labels as $label) {
            $label = trim($label);
            if ($label == self::DEFAULT_LANGUAGE_LABEL || $xml->isElementPresent($label)) {
                continue;
            }
            $body->appendChild($xml->createTranslationElement($target, $label));
        }

        return in_array(self::DEFAULT_LANGUAGE_LABEL, $labels);
    }

    /**
     * {@inheritDoc}
     */
    public static function formatColumnName($number)
    {
        for ($char = ""; $number >= 0; $number = intval($number / 26) - 1) {
            $char = chr($number%26 + 0x41) . $char;
        }
        return $char;
    }
}
