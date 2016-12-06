<?php

namespace Tests;

use KunicMarko\StaticTranslationBundle\Command\StaticTranslateCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

class StaticTranslateCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new StaticTranslateCommand());

        $command = $application->find('static:translation');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),

            // pass arguments to the helper
            'file' => 'test4.xlsx',
            'languages' => 'en de'
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Username: Wouter', $output);

        // ...
    }
}