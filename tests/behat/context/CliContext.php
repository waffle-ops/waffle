<?php

namespace Waffle\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use TitasGailius\Terminal\Terminal;
use PHPUnit\Framework\Assert as Assertions;

/**
 * Defines application features from the specific context.
 */
class CliContext implements Context
{

    protected $cli;

    /**
     * @Given I run Waffle from the command line
     */
    public function iRunWaffleFromTheCommandLine()
    {
        // Intentionally blank. This is technically not needed.
    }

    /**
     * @When I run :arg1
     */
    public function iRun($arg1)
    {
        // @todo This should be updated to use the bootstrap waffle.php file.
        // Or, perhaps the built .phar file if we can make it configurable.
        $command = sprintf('wfl %s', $arg1);
        $this->cli = Terminal::run($command);
    }

    /**
     * @Then I should see :arg1
     */
    public function iShouldSee($arg1)
    {
        Assertions::assertStringContainsString($arg1, $this->cli->output());
    }
}
