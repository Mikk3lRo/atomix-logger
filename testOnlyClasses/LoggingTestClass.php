<?php declare(strict_types = 1);

use Mikk3lRo\atomix\logger\LogTrait;

class LoggingTestClass
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
