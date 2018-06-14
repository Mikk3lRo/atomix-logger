<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\io\Logger;
use Mikk3lRo\atomix\io\LogTrait;

class loggingClass {
    use LogTrait;
    function testDebug() {
        $this->log()->debug('This is debug level...');
    }
    function testEmergency() {
        $this->log()->emergency('This is emergency level...');
    }
}

final class LogTraitTest extends TestCase
{
    private $log_pcre_prefix = '(\[[^\]]+\]\s*){3}\s';

    public function testCanInstantiate() {
        $loggingClass = new loggingClass();
        $this->assertInstanceOf(loggingClass::class, $loggingClass);
        return $loggingClass;
    }
    /**
     * @depends testCanInstantiate
     */
    public function testNullLogger(loggingClass $loggingClass) {
        $this->expectOutputString('');
        $loggingClass->testEmergency();
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanRedefineLogger(loggingClass $loggingClass) {
        $outputlogger = new Logger();
        $loggingClass->setLogger($outputlogger);
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'This is emergency level...$#m');
        $loggingClass->testEmergency();
    }
}