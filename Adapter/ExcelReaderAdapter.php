<?php

namespace KunicMarko\StaticTranslationsBundle\Adapter;

use PHPExcel_IOFactory;

/**
 * Created by PhpStorm.
 * User: markokunic
 * Date: 4/12/17
 * Time: 1:25 PM.
 */
class ExcelReaderAdapter implements ExcelReaderAdapterInterface
{
    /** @var \PHPExcel_Worksheet */
    private $worksheet;

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($file);
        $this->worksheet = $objPHPExcel->getActiveSheet();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastRow()
    {
        return $this->worksheet->getHighestRow();
    }

    /**
     * {@inheritdoc}
     */
    public function getCell($cell)
    {
        return trim($this->worksheet->getCell($cell));
    }
}
