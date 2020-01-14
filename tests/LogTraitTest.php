<?php declare(strict_types = 1);

namespace Mikk3lRo\Tests;

use LoggingTestClass;
use Mikk3lRo\atomix\logger\OutputLogger;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../testOnlyClasses/LoggingTestClass.php';

/**
 * @covers Mikk3lRo\atomix\logger\AbstractLogger
 * @covers Mikk3lRo\atomix\logger\LogTrait
 */
final class LogTraitTest extends TestCase
{
    private $logPcrePrefix = '(\[[^\]]+\]\s*){3}\s';


    public function testCanInstantiate()
    {
        $loggingClass = new LoggingTestClass();
        $this->assertInstanceOf(LoggingTestClass::class, $loggingClass);
    }


    public function testNullLogger()
    {
        $this->expectOutputString('');
        (new LoggingTestClass)->testEmergency();
    }


    public function testCanRedefineLogger()
    {
        $loggingClass = new LoggingTestClass;
        $loggingClass->setLogger(new OutputLogger);
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'This is emergency level...$#m');
        $loggingClass->testEmergency();
    }
}
