<?php
namespace Mikk3lRo\atomix\io;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LogTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger The logger instance to use.
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) : void
    {
        $this->logger = $logger;
    }


    /**
     * Gets the logger.
     *
     * @return LoggerInterface Returns the logger instance.
     */
    protected function log() : LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }
}
