<?php

namespace StaticTranslationBundle\Command;

use PHPExcel_IOFactory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StaticTranslateCommand extends ContainerAwareCommand
{
    const TranslationDirectory = 'app/Resources/translations/'; 
    
    private $xml;
    private $file;
    private $currentLanguage;
    private $body;
    private $sourceLanguage;
    private $labelColumn;
    protected function configure()
    {
        $this
            ->setName('static:translation')
            ->setDescription('Translate static strings in application.')
            ->setDefinition(array(
                new InputArgument('file', InputArgument::REQUIRED, 'Path to Excel file with data for translation'),
                new InputArgument('languages', InputArgument::IS_ARRAY, 'Array of language codes ( en de fr ).'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->file   = $input->getArgument('file');
        $languages    = $input->getArgument('languages');
        $this->sourceLanguage = $languages[0];
        $this->labelColumn = $this->num2alpha(count($languages));

        foreach($languages as $k => $language){
            
            $this->currentLanguage = $this->num2alpha($k);
            $this->xml = new \DOMDocument('1.0', 'utf-8');

            if($old = file_exists(self::TranslationDirectory.'messages.'.$language.'.xliff')){
                $this->xml->load(self::TranslationDirectory.'messages.'.$language.'.xliff');
                $this->body = $this->xml->getElementsByTagName('body')[0];
                
            }else{
                $this->body = $this->xml->createElement('body');
      
            }
            $this->handleBody();
            $this->save($language, $old); 
        }
           
    }
    //prepare body for xml
    private function handleBody(){       
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($this->file);
        
        $worksheet = $objPHPExcel->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        for ($row = 3; $row <= $lastRow; $row++) {
                        
            $target = $worksheet->getCell($this->currentLanguage.$row);
            
            if($worksheet->getCell($this->labelColumn.$row) != "" && !$this->handleLabels($worksheet,$row,$target)){
                continue;    
            }
            
            $source = $this->currentLanguage == 'A' ? $target : $worksheet->getCell('A'.$row); 
            if($this->checkIfIdExists(str_replace(' ', '_', $source))) continue;
            $this->body->appendChild($this->createTranslationElement($target,$source));
            
        } 
    }
    //if we added labels ( for sonata admin maybe ), add for every label row in xml
    private function handleLabels($worksheet,$row,$target){
        $labels = explode(',',$worksheet->getCell($this->labelColumn.$row));
        foreach($labels as $label){
            $label = trim($label);
            if($label == 'default.language.source') continue;
            if($this->checkIfIdExists(str_replace(' ', '_', $label))) continue;
            $this->body->appendChild($this->createTranslationElement($target,$label)); 
        }
        
        if(in_array("default.language.source", $labels)){
           return true;
        }
        
        return false;
    }

    //if translation file does not exit, create it and save
    private function save($lang,$old = false){
        if(!$old){
            $xliff = $this->xml->createElement('xliff');
            $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
            $xliff->setAttribute('version', '1.2');

            $file = $this->xml->createElement('file');
            $file->setAttribute('source-language', $this->sourceLanguage);
            $file->setAttribute('datatype', 'plaintext');
            $file->setAttribute('original', 'file.ext');        

            $file->appendChild($this->body);
            $xliff->appendChild($file);
            $this->xml->appendChild( $xliff );

            if(!file_exists(self::TranslationDirectory)){
                exec('mkdir '.self::TranslationDirectory);
            }
        }
        
        $this->xml->save(self::TranslationDirectory.'messages.'.$lang.'.xliff'); 
    }
    //give us Excel Column letter for a number
    private function num2alpha($n)
    {
        for($r = ""; $n >= 0; $n = intval($n / 26) - 1){
            $r = chr($n%26 + 0x41) . $r;   
        }
        return $r;
    }
    
    //create trans-unit
    private function createTranslationElement($target,$source){
        $trans = $this->xml->createElement('trans-unit');
        $trans->setAttribute('id', str_replace(' ', '_', $source));
        $trans->appendChild($this->createTextElement('source',$source));
        $trans->appendChild($this->createTextElement('target',$target));  
        return $trans;
    }
    // create trans-unit body
    private function createTextElement($el,$text){
        $element = $this->xml->createElement($el);
        $element_text = $this->xml->createTextNode($text);
        $element->appendChild($element_text);
        return $element;
    }
    private function checkIfIdExists($name) {
        $translations = $this->xml->getElementsByTagName('trans-unit');
        
        foreach ($translations as $translation){
            if ($translation->getAttribute('id') == $name){
                return true;
            }     
        }         
        return false;
    }
    
        /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Welcome to Static Translations');
        $style->section('Excel formating:');
        $style->table(
            ['','A', 'B'],
            [
                [1,'English', 'German'],
                [2,'',''],
                [3,'About Us', 'Über uns','label.about'],
                [4,'Contact', 'Kontakt',],
                [5,'Imprint','Impressum','form.about, default.language.source']
            ]
        );
        $style->text(['We expect words for translation to start from line 3'
        ,'Labels are optional, there can be more than one label for same word, they just have to be devided by commma (,)'
        ,'If you add labels, label names will be used for source translation tags in xml'
        ,'if you want to use default language word for source and use lables for same word, you can use reserved word "default.language.source" and add it in labels part']);

        if (!$input->getArgument('file')) {
            $style->section('Excel file has to end with .xlsx');
            $answer = $style->ask('Please provide path to Excel file', null, function ($file) {
                if (empty($file)) {
                    throw new \RuntimeException('Path to Excel file has to be provided');
                }
                if(!preg_match('/\w+\.xlsx/', $file)){
                    throw new \RuntimeException('Excel file has to end with .xlsx');
                }
                if(!file_exists($file)){
                    throw new \RuntimeException('Excel file was not found');
                }

                return $file;
            });
            $input->setArgument('file', $answer);
            $style->newLine();
        }

        if (!$input->getArgument('languages')) {
            $style->section('We expect array of language codes, diveded by space e.g. ( en de fr ), use same order as in your excel file');
            $style->note('Don\'t forget that first language in array is source language and will be used for all source tags');
            $answer = $style->ask('Please provide array of language codes', null, function ($languages) {
                if (empty($languages)) {
                    throw new \Exception('Languages can\'t be empty');
                }

                return $languages;
            });
            $input->setArgument('languages', explode(' ',$answer));
        }
    }
}
