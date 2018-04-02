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
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gets the logger.
     */
    protected function log() : LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }
}
