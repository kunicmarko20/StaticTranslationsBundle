<?php

namespace KunicMarko\StaticTranslationsBundle\Command;

use PHPExcel_IOFactory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KunicMarko\StaticTranslationsBundle\Services\HelperService;

class TranslateCommand extends ContainerAwareCommand
{

    const DefaultLanguageLabel = 'default.language.source';
    const TranslationFileName  = 'messages.%s.xliff';
    
    private $xml;
    private $currentLanguage;
    private $body;
    private $labelColumn;
    private $translationDirectory;
    private $helper;
    
    public function __construct($dir,HelperService $helper, $name = null) {
        $this->translationDirectory = $dir;
        $this->helper = $helper;
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this
            ->setName('generate:static:translations')
            ->setDescription('Generate translation files for static strings in application.')
            ->setDefinition(array(
                new InputArgument('file', InputArgument::REQUIRED, 'Path to Excel file with data for translation'),
                new InputArgument('languages', InputArgument::IS_ARRAY, 'Array of language codes ( en de fr ).'),
            ))
            ->setHelp($this->helper->getHelpText())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {  
        if(!$languages = $input->getArgument('languages')){
           throw new \Exception('Languages can\'t be empty');
        }
        $file = $input->getArgument('file');
        $sourceLanguage = $languages[0];
        $this->labelColumn = $this->helper->num2alpha(count($languages));

        foreach($languages as $k => $language){
            
            $this->currentLanguage = $this->helper->num2alpha($k);
            $this->xml = new \DOMDocument('1.0', 'utf-8');

            if($old = file_exists($this->getTranslationFileName($language))){
                $this->xml->load($this->getTranslationFileName($language));
                $this->body = $this->xml->getElementsByTagName('body')[0];
                
            }else{
                $this->body = $this->xml->createElement('body');
      
            }
            $this->helper->setXml($this->xml);
            $this->handleBody($file);
            $this->save($language, $sourceLanguage, $old); 
        }
           
    }
    //prepare body for xml
    private function handleBody($file){       
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($file);
        
        $worksheet = $objPHPExcel->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        for ($row = 3; $row <= $lastRow; $row++) {
            
            $target = trim($worksheet->getCell($this->currentLanguage.$row));
            $source = trim($worksheet->getCell('A'.$row));
            
            if(empty($target) && empty($source)) break;         
            
            if($worksheet->getCell($this->labelColumn.$row) != "" && !$this->handleLabels($worksheet,$row,$target)) continue;
            
            $source = $this->currentLanguage != 'A' ? $source : $target; 
            if($this->helper->checkIfIdExists($source)) continue;
            $this->body->appendChild($this->helper->createTranslationElement($target,$source));
            
        } 
    }
    //if we added labels ( for sonata admin maybe ), add for every label row in xml
    private function handleLabels($worksheet,$row,$target){
        $labels = explode(',',$worksheet->getCell($this->labelColumn.$row));
        foreach($labels as $label){
            $label = trim($label);
            if($label == self::DefaultLanguageLabel) continue;
            if($this->helper->checkIfIdExists($label)) continue;
            $this->body->appendChild($this->helper->createTranslationElement($target,$label)); 
        }
        
        if(in_array(self::DefaultLanguageLabel, $labels)){
           return true;
        }
        
        return false;
    }

    //if translation file does not exit, create it and save
    private function save($language, $sourceLanguage, $old = false){
        if(!$old){
            
            $xliff = $this->helper->createElement('xliff',[
                'xmlns' => 'urn:oasis:names:tc:xliff:document:1.2',
                'version' => '1.2'
            ]);

            $file = $this->helper->createElement('file',[
                'source-language' => $sourceLanguage,
                'datatype' => 'plaintext',
                'original' => 'file.ext'
            ]);
       

            $file->appendChild($this->body);
            $xliff->appendChild($file);
            $this->xml->appendChild( $xliff );

            if(!file_exists($this->translationDirectory)){
                exec('mkdir '.$this->translationDirectory);
            }
        }
        
        $this->xml->save($this->getTranslationFileName($language)); 
    }
    //Name of file with language extension
    private function getTranslationFileName($language){
        return $this->translationDirectory.sprintf(self::TranslationFileName,$language);
    }
     /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        
        if(!$input->getArgument('file') && !$input->getArgument('languages')){
            $style->title('Welcome to Static Translations');            
        }
        
        if (!$input->getArgument('file')) {
            $style->section('Excel formating:');
            $style->table(
                ['','A', 'B'],
                [
                    [1,'English', 'German'],
                    [2,'',''],
                    [3,'About Us', 'Über uns','label.about'],
                    [4,'Contact', 'Kontakt'],
                    [5,'Imprint','Impressum','form.about, default.language.source']
                ]
            );
            $style->text([
                'We expect words for translation to start from line 3',
                'You can add more languages, we only expect labels to be at last position',
                'Labels are optional, there can be more than one label for same word, they just have to be divided by comma (,)',
                'If you add labels, label names will be used for source translation tags in xml',
                'if you want to use default language word for source and use labels for same word, you can use reserved word "default.language.source" and add it in labels part'
            ]);
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
            $style->section('We expect array of language codes, divided by space e.g. ( en de fr ), use same order as in your excel file');
            $style->note('First language in array is source language and will be used for all source tags');
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
