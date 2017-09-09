<?php
/**
 * Created by PhpStorm.
 * User: markokunic
 * Date: 4/13/17
 * Time: 3:06 PM.
 */

namespace KunicMarko\StaticTranslationsBundle\Parser;

use KunicMarko\StaticTranslationsBundle\XML\XMLDocument;

interface ParserStrategyInterface
{
    /**
     * Parse file and add new translations to XMLDocument object.
     *
     * @param XMLDocument $xml
     * @param string      $currentLanguageColumn
     * @param string      $labelColumn
     *
     * @return void
     */
    public function parse(XMLDocument $xml, $currentLanguageColumn, $labelColumn);

    /**
     * Formats number of column to appropriate name depending on parser.
     *
     * @param int $number
     *
     * @return string
     */
    public static function formatColumnName($number);
}
