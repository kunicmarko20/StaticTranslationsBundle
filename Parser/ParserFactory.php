<?php

namespace KunicMarko\StaticTranslationsBundle\Parser;

use KunicMarko\StaticTranslationsBundle\Adapter\ExcelReaderAdapter;

class ParserFactory
{
    /**
     * Create new Parser based on file extension
     * @param string $file
     * @return ExcelParser
     * @throws \RuntimeException
     */
    public static function build($file)
    {
        if (preg_match('/\w+\.xlsx/', $file)) {
            $excelReader = new ExcelReaderAdapter();
            $excelReader->load($file);

            return new ExcelParser($excelReader);
        }
        //maybe add additional parser (CSV)
        throw new \RuntimeException('Please provide Excel file for parsing.');
    }
}
