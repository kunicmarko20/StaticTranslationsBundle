<?php

namespace KunicMarko\StaticTranslationsBundle\Command;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use KunicMarko\StaticTranslationsBundle\Parser\ParserFactory;
use KunicMarko\StaticTranslationsBundle\XML\XMLDocument;

use KunicMarko\StaticTranslationsBundle\XML\XMLDocumentFactory;

class TranslateCommand extends ContainerAwareCommand
{
    /** @var string */
    private $translationDirectory;
    
    public function __construct(string $directory, $name = null)
    {
        $this->translationDirectory = $directory;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = $input->getArgument('languages');
        $file = $input->getArgument('file');
        $sourceLanguage = $languages[0];
        $parser = ParserFactory::build($file);
        $labelColumn = $parser::formatColumnName(count($languages));
        
        foreach ($languages as $index => $language) {
            $xml = XMLDocumentFactory::build($sourceLanguage, $this->translationDirectory, $language);
            $parser->parse($xml, $parser::formatColumnName($index), $labelColumn);
            $xml->save();
        }
        $output->writeln('<info>Success, files generated.</info>');
    }
   
     /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        if (!$input->getArgument('file')) {
            $answer = $style->ask('Please provide path to Excel file', null, function ($file) {
                if (!file_exists($file)) {
                    throw new \RuntimeException('Excel file was not found');
                }
                return $file;
            });
            $input->setArgument('file', $answer);
            $style->newLine();
        }

        if (!$input->getArgument('languages')) {
            $style->note('We expect array of language codes, divided by space e.g. ( en de fr ),
            use same order as in your excel file');
            $answer = $style->ask('Please provide array of language codes', null, function ($languages) {
                if (empty($languages)) {
                    throw new \Exception('Languages can\'t be empty');
                }
                return $languages;
            });
            $input->setArgument('languages', explode(' ', $answer));
        }
    }
}
