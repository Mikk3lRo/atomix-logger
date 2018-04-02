<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\io\Logger;
use Psr\Log\LogLevel;
use Mikk3lRo\atomix\system\DirConf;

DirConf::define('log', '/tmp/testlogs');

final class LoggerTest extends TestCase
{
    private $log_pcre_prefix = '(\[[^\]]+\]\s*){3}\s';
    public function testCanInstantiate() {
        $logger = new Logger();
        $this->assertInstanceOf(Logger::class, $logger);
        return $logger;
    }

    /**
     * @depends testCanInstantiate
     */
    public function testCanWrite(Logger $logger) {
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'And it was written...$#');
        $logger->error('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanWriteNonString1(Logger $logger) {
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'false$#');
        $logger->error(false);
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanWriteNonString2(Logger $logger) {
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'array \(#');
        $logger->error(array(1, 2, 3));
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanReplaceContext(Logger $logger) {
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'I have an apple and a banana#');
        $logger->error('I have an {fruit1} and a {fruit2}', array(
            'fruit1' => 'apple',
            'fruit2' => 'banana'
        ));
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsLogLevels1(Logger $logger) {
        $logger->set_max_log_level(LogLevel::WARNING);
        $this->expectOutputString('');
        $logger->debug('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsLogLevels2(Logger $logger) {
        $logger->set_max_log_level(LogLevel::DEBUG);
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'And it was written...$#');
        $logger->debug('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanSetAndGetAllLogLevels(Logger $logger) {
        $logger->set_max_log_level(LogLevel::DEBUG);
        $this->assertEquals(LogLevel::DEBUG, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::NOTICE);
        $this->assertEquals(LogLevel::NOTICE, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::WARNING);
        $this->assertEquals(LogLevel::WARNING, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::ERROR);
        $this->assertEquals(LogLevel::ERROR, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::CRITICAL);
        $this->assertEquals(LogLevel::CRITICAL, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $logger->get_max_log_level());

        $logger->set_max_log_level(LogLevel::EMERGENCY);
        $this->assertEquals(LogLevel::EMERGENCY, $logger->get_max_log_level());
    }
    /**
     * @depends testCanInstantiate
     */
    public function testCanSetNumericLogLevel(Logger $logger) {
        $logger->set_max_log_level(4);
        $this->assertEquals(4, $logger->get_max_log_level(true));
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsIndent1(Logger $logger) {
        $logger->indent_increase();
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . '    And it was written...$#');
        $logger->error('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsIndent2(Logger $logger) {
        $logger->indent_increase();
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . '        And it was written...$#');
        $logger->error('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsIndent3(Logger $logger) {
        $logger->indent_decrease();
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . '    And it was written...$#');
        $logger->error('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testRespectsIndent4(Logger $logger) {
        $logger->indent_decrease();
        $this->expectOutputRegex('#^' . $this->log_pcre_prefix . 'And it was written...$#');
        $logger->error('And it was written...');
    }
    /**
     * @depends testCanInstantiate
     */
    public function testWriteToAbsFile(Logger $logger) {
        $tmpname = '/tmp/logtest1';
        if (file_exists($tmpname)) {
            unlink($tmpname);
        }

        $logger->set_log_filename($tmpname);
        $logger->set_output(false);
        $logger->error('And it was written...');

        $this->assertFileExists($tmpname);

        $this->assertRegExp('#^' . $this->log_pcre_prefix . 'And it was written...$#', file_get_contents($tmpname));

        unlink($tmpname);
    }
    /**
     * @depends testCanInstantiate
     */
    public function testWriteToRelFile(Logger $logger) {
        $logger->set_log_filename('logtest2');
        $logfile_actual = $logger->get_log_filename();

        $this->assertEquals('/tmp/testlogs/logtest2', $logfile_actual);

        if (file_exists($logfile_actual)) {
            unlink($logfile_actual);
        }

        $logger->set_output(false);
        $logger->error('And it was written...');

        $this->assertFileExists($logfile_actual);

        $this->assertRegExp('#^' . $this->log_pcre_prefix . 'And it was written...$#', file_get_contents($logfile_actual));

        unlink($logfile_actual);
    }
    /**
     * @depends testCanInstantiate
     */
    public function testFailsToSetNonStringFilename(Logger $logger) {
        $logger->set_log_filename('logtest3');
        $this->assertEquals('/tmp/testlogs/logtest3', $logger->get_log_filename());

        $logger->set_log_filename(true);
        $this->assertEquals(false, $logger->get_log_filename());

        $logger->set_log_filename('logtest4');
        $this->assertEquals('/tmp/testlogs/logtest4', $logger->get_log_filename());

        $logger->set_log_filename(array('this should fail', 'SO badly'));
        $this->assertEquals(false, $logger->get_log_filename());
    }
}