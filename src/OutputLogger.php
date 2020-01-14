<?php declare(strict_types = 1);

namespace Mikk3lRo\atomix\logger;

use Mikk3lRo\atomix\logger\AbstractLogger;

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
