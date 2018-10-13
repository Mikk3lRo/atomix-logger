<?php
namespace Mikk3lRo\atomix\io;

use Mikk3lRo\atomix\io\AbstractLogger;

class OutputLogger extends AbstractLogger
{
    /**
     * @param string $output The output.
     *
     * @return void
     */
    protected function output(string $output): void
    {
        echo $output;
    }
}
