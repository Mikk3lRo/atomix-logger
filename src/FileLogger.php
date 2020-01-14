<?php declare(strict_types = 1);

namespace Mikk3lRo\atomix\logger;

use Mikk3lRo\atomix\logger\AbstractLogger;

class FileLogger extends AbstractLogger
{
    /**
     * @var string Log filename
     */
    private $filename = null;


    /**
     * Instantiate.
     *
     * @param string $filename Log filename.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }


    /**
     * @param string $output The output.
     *
     * @return void
     */
    protected function output(string $output): void
    {
        file_put_contents($this->filename, $output, FILE_APPEND);
    }
}
