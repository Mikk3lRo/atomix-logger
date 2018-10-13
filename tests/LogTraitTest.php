<?php
declare(strict_types=1);

namespace Mikk3lRo\atomix\Tests;

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\io\OutputLogger;

require_once __DIR__ . '/testOnlyClasses/LoggingClass.php';

final class LogTraitTest extends TestCase
{
    private $logPcrePrefix = '(\[[^\]]+\]\s*){3}\s';


    public function testCanInstantiate()
    {
        $loggingClass = new LoggingClass();
        $this->assertInstanceOf(LoggingClass::class, $loggingClass);
    }


    public function testNullLogger()
    {
        $this->expectOutputString('');
        (new LoggingClass)->testEmergency();
    }


    public function testCanRedefineLogger()
    {
        $loggingClass = new LoggingClass;
        $loggingClass->setLogger(new OutputLogger);
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'This is emergency level...$#m');
        $loggingClass->testEmergency();
    }
}
