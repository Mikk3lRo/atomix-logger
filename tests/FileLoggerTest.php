<?php declare(strict_types = 1);

namespace Mikk3lRo\Tests;

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\logger\FileLogger;
use Psr\Log\LoggerInterface;
use TypeError;

/**
 * @covers Mikk3lRo\atomix\logger\AbstractLogger
 * @covers Mikk3lRo\atomix\logger\FileLogger
 */
final class FileLoggerTest extends TestCase
{
    private $logPcrePrefix = '(\[[^\]]+\]\s*){3}\s';


    public function testCanInstantiate()
    {
        $logger = new FileLogger('/tmp/logtest1');
        $this->assertInstanceOf(FileLogger::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }


    public function testWriteToFile()
    {
        $tmpname = '/tmp/logtest1';
        if (file_exists($tmpname)) {
            unlink($tmpname);
        }

        $logger = new FileLogger($tmpname);

        $logger->error('And it was written...');

        $this->assertFileExists($tmpname);

        $this->assertRegExp('#^' . $this->logPcrePrefix . 'And it was written...$#m', file_get_contents($tmpname));

        unlink($tmpname);
    }


    public function testFailsToSetNonStringFilename()
    {
        $this->expectException(TypeError::class);
        new FileLogger(true);
    }


    public function testFailsToSetNonStringFilename2()
    {
        $this->expectException(TypeError::class);
        new FileLogger(array('this should fail', 'SO badly'));
    }
}
