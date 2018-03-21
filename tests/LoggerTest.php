<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\io\Logger;
use Mikk3lRo\atomix\system\DirConf;

DirConf::define('log', '/tmp/testlogs');

final class LoggerTest extends TestCase
{
    public function testCanWrite() {
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#');
        Logger::write('And it was written...');
    }
    public function testCanWriteNonString1() {
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\sfalse$#');
        Logger::write(false);
    }
    public function testCanWriteNonString2() {
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\] array \(#');
        Logger::write(array(1, 2, 3));
    }
    public function testIgnoresHighLogLevels() {
        $this->expectOutputString('');
        Logger::write('And it was written...', 999);
    }
    public function testCanSetLogLevel() {
        Logger::set_max_log_level(1000);
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#');
        Logger::write('And it was written...', 999);
    }
    public function testCanGetLogLevel() {
        Logger::get_max_log_level();
        $this->assertEquals(1000, Logger::get_max_log_level());
        Logger::set_max_log_level(4);
        $this->assertEquals(4, Logger::get_max_log_level());
    }
    public function testRespectsIndent1() {
        Logger::indent_increase();
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\s    And it was written...$#');
        Logger::write('And it was written...');
    }
    public function testRespectsIndent2() {
        Logger::indent_increase();
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\s        And it was written...$#');
        Logger::write('And it was written...');
    }
    public function testRespectsIndent3() {
        Logger::indent_decrease();
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\s    And it was written...$#');
        Logger::write('And it was written...');
    }
    public function testRespectsIndent4() {
        Logger::indent_decrease();
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#');
        Logger::write('And it was written...');
    }
    
    public function testWriteToAbsFile() {
        $tmpname = '/tmp/logtest1';
        if (file_exists($tmpname)) {
            unlink($tmpname);
        }
        
        Logger::set_log_filename($tmpname);
        Logger::set_output(false);
        Logger::write('And it was written...');
        
        $this->assertFileExists($tmpname);
        
        $this->assertRegExp('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#', file_get_contents($tmpname));
        
        unlink($tmpname);
    }
    
    public function testWriteToRelFile() {
        Logger::set_log_filename('logtest2');
        $logfile_actual = Logger::get_log_filename();
        
        $this->assertEquals('/tmp/testlogs/logtest2', $logfile_actual);
        
        if (file_exists($logfile_actual)) {
            unlink($logfile_actual);
        }
        
        Logger::set_output(false);
        Logger::write('And it was written...');
        
        $this->assertFileExists($logfile_actual);
        
        $this->assertRegExp('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#', file_get_contents($logfile_actual));
        
        unlink($logfile_actual);
    }
    
    public function testFailsToSetNonStringFilename() {
        Logger::set_log_filename('logtest3');
        $this->assertEquals('/tmp/testlogs/logtest3', Logger::get_log_filename());
        
        Logger::set_log_filename(true);
        $this->assertEquals(false, Logger::get_log_filename());
        
        Logger::set_log_filename('logtest4');
        $this->assertEquals('/tmp/testlogs/logtest4', Logger::get_log_filename());
        
        Logger::set_log_filename(array('this should fail', 'SO badly'));
        $this->assertEquals(false, Logger::get_log_filename());
    }
}