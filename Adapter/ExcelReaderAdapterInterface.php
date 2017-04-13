<?php

namespace KunicMarko\StaticTranslationsBundle\Adapter;

interface ExcelReaderAdapterInterface
{
    /**
     * Load Excel file
     * @param string $file
     * @return void
     */
    public function load($file);

    /**
     * Get last row of Excel file
     * @return int
     */
    public function getLastRow();

    /**
     * Get Cell from Excel file
     * @param string $cell
     * @return \PHPExcel_Cell
     */
    public function getCell($cell);
}
