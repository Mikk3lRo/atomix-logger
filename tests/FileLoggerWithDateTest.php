<?php declare(strict_types = 1);

namespace Mikk3lRo\Tests;

use Mikk3lRo\atomix\logger\FileLoggerWithDate;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TypeError;

/**
 * @covers Mikk3lRo\atomix\logger\AbstractLogger
 * @covers Mikk3lRo\atomix\logger\FileLoggerWithDate
 */
final class FileLoggerWithDateTest extends TestCase
{
    private $logPcrePrefix = '(\[[^\]]+\]\s*){3}\s';


    public function testCanInstantiate()
    {
        $logger = new FileLoggerWithDate('/tmp/logtest2');
        $this->assertInstanceOf(FileLoggerWithDate::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }


    public function testWriteToFile()
    {
        $tmpname = '/tmp/logtest1';
        if (file_exists($tmpname)) {
            unlink($tmpname);
        }

        $logger = new FileLoggerWithDate($tmpname);

        $logger->error('And it was written...');

        $this->assertFileExists($tmpname);

        $this->assertRegExp('#^' . $this->logPcrePrefix . 'And it was written...$#m', file_get_contents($tmpname));

        unlink($tmpname);
    }


    public function testWriteToFileWithDate()
    {
        $filename = '/tmp/logtest_[DATE]';
        $realname = '/tmp/logtest_' . date('Y_m_d');
        if (file_exists($realname)) {
            unlink($realname);
        }

        $logger = new FileLoggerWithDate($filename);

        $logger->error('And it was written...');

        $this->assertFileExists($realname);

        $this->assertRegExp('#^' . $this->logPcrePrefix . 'And it was written...$#m', file_get_contents($realname));

        unlink($realname);
    }


    public function testFailsToSetNonStringFilename()
    {
        $this->expectException(TypeError::class);
        new FileLoggerWithDate(true);
    }


    public function testFailsToSetNonStringFilename2()
    {
        $this->expectException(TypeError::class);
        new FileLoggerWithDate(array('this should fail', 'SO badly'));
    }
}
