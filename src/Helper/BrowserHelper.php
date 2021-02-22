<?php

namespace Waffle\Helper;

use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliCommand;

class BrowserHelper
{

    /**
     * Attempts to open a web browser to the suppleid url.
     *
     * @return void
     */
    public function openBrowser($url)
    {
        $browser = $this->getPotentialBrowser();

        if (empty($browser)) {
            throw new \Exception('Unable to open web browser.');
        }

        $command = new BaseCliCommand([$browser, $url]);
        $process = $command->getProcess();
        $process->run();
    }

    /**
     * Gets the shell command to open the browser.
     *
     * @return string
     */
    private function getPotentialBrowser()
    {
        // TODO - This will not work in Windows.
        $programs = ['xdg-open', 'open'];

        foreach ($programs as $program) {
            if ($this->programExists($program)) {
                return $program;
            }
        }

        return null;
    }

    /**
     * Checks if a 'which' call returns sucessfully.
     *
     * @return Process
     */
    private function programExists($program)
    {
        $command = new BaseCliCommand(['which', $program]);
        $process = $command->getProcess();
        $process->run();
        return $process->isSuccessful();
    }
}
