<?php declare(strict_types = 1);

namespace Mikk3lRo\atomix\Tests;

use Mikk3lRo\atomix\io\LogTrait;

class LoggingClass
{
    use LogTrait;


    public function testDebug()
    {
        $this->log()->debug('This is debug level...');
    }


    public function testEmergency()
    {
        $this->log()->emergency('This is emergency level...');
    }
}
