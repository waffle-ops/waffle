<?php

namespace Waffle\Tests\Command\Command;

use Waffle\Tests\TestCase;

class HelpTest extends TestCase
{
    public function testHelp()
    {
        $tester = static::getCommandTester('help');
        $tester->execute([]);
        $output = $tester->getDisplay();

        $this->assertStringContainsString('Display help for a command', $output);
    }

    /**
     * @todo This is an example to show how we can use PHP Unit to test Waffle.
     * The help call, is likely best suited for each individual command test
     * file.
     */
    public function testHelpDocs()
    {
        $tester = static::getCommandTester('help');
        $tester->execute(['command_name' => 'docs']);
        $output = $tester->getDisplay();

        $this->assertStringContainsString('Opens a web browser to the Waffle documentation', $output);
        $this->assertStringContainsString('Prevents Waffle from attempting to open a browser tab to the docs page', $output);
    }
}
