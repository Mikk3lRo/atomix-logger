<?php declare(strict_types = 1);

namespace Mikk3lRo\atomix\io;

use Mikk3lRo\atomix\io\AbstractLogger;

class FileLoggerWithDate extends AbstractLogger
{
    /**
     * @var string Log filename
     */
    private $filename = null;


    /**
     * Instantiate.
     *
     * @param string $filename Log filename - use [DATE] where the date should be.
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
        file_put_contents(str_replace('[DATE]', date('Y_m_d'), $this->filename), $output, FILE_APPEND);
    }
}
