<?php
declare(strict_types=1);

namespace Mikk3lRo\atomix\Tests;

use PHPUnit\Framework\TestCase;

use stdClass;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Mikk3lRo\atomix\io\OutputLogger;

final class OutputLoggerTest extends TestCase
{
    private $logPcrePrefix = '(\[[^\]]+\]\s*){3}\s';


    public function testCanInstantiate()
    {
        $logger = new OutputLogger();
        $this->assertInstanceOf(OutputLogger::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }


    public function testCanWrite()
    {
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'And it was written...$#m');
        (new OutputLogger)->error('And it was written...');
    }


    public function testCanWriteNonString1()
    {
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'false$#m');
        (new OutputLogger)->error(false);
    }


    public function testCanWriteNonString2()
    {
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'array \(#');
        (new OutputLogger)->error(array(1, 2, 3));
    }


    public function testCanReplaceContext()
    {
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'I have an apple and a banana\n#');
        (new OutputLogger)->error('I have an {fruit1} and a {fruit2}', array(
            'fruit1' => 'apple',
            'fruit2' => 'banana'
        ));
    }


    public function testCanReplaceContext2()
    {
        $object = new stdClass();
        $object->var = 'value';

        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'String: STRING, Int: 123, Float: 1.23, Bool: 1, Array: array\(3\), Object: object:stdClass#');
        (new OutputLogger)->error('String: {string}, Int: {int}, Float: {float}, Bool: {bool}, Array: {array}, Object: {object}', array(
            'string' => 'STRING',
            'int' => 123,
            'float' => 1.23,
            'bool' => true,
            'array' => array('a', 'b', 'c'),
            'object' => $object
        ));
    }


    public function testRespectsLogLevels1()
    {
        $logger = new OutputLogger;
        $logger->setMaxLogLevel(LogLevel::WARNING);
        $this->expectOutputString('');
        $logger->debug('And it was written...');
    }


    public function testRespectsLogLevels2()
    {
        $logger = new OutputLogger;
        $logger->setMaxLogLevel(LogLevel::DEBUG);
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'And it was written...$#');
        $logger->debug('And it was written...');
    }


    public function testCanSetAndGetAllLogLevels()
    {
        $logger = new OutputLogger;
        $logger->setMaxLogLevel(LogLevel::DEBUG);
        $this->assertEquals(LogLevel::DEBUG, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::NOTICE);
        $this->assertEquals(LogLevel::NOTICE, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::WARNING);
        $this->assertEquals(LogLevel::WARNING, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::ERROR);
        $this->assertEquals(LogLevel::ERROR, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::CRITICAL);
        $this->assertEquals(LogLevel::CRITICAL, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $logger->getMaxLogLevel());

        $logger->setMaxLogLevel(LogLevel::EMERGENCY);
        $this->assertEquals(LogLevel::EMERGENCY, $logger->getMaxLogLevel());
    }


    public function testCanSetAndGetAllBacktraceLevels()
    {
        $logger = new OutputLogger;
        $logger->setMaxBacktraceLevel(LogLevel::DEBUG);
        $this->assertEquals(LogLevel::DEBUG, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::NOTICE);
        $this->assertEquals(LogLevel::NOTICE, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::WARNING);
        $this->assertEquals(LogLevel::WARNING, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::ERROR);
        $this->assertEquals(LogLevel::ERROR, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::CRITICAL);
        $this->assertEquals(LogLevel::CRITICAL, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $logger->getMaxBacktraceLevel());

        $logger->setMaxBacktraceLevel(LogLevel::EMERGENCY);
        $this->assertEquals(LogLevel::EMERGENCY, $logger->getMaxBacktraceLevel());
    }


    public function testCanSetNumericLogLevel()
    {
        $logger = new OutputLogger;
        $logger->setMaxLogLevel(4);
        $this->assertEquals(4, $logger->getMaxLogLevel(true));
    }


    public function testCanSetNumericBacktraceLevel()
    {
        $logger = new OutputLogger;
        $logger->setMaxBacktraceLevel(3);
        $this->assertEquals(3, $logger->getMaxBacktraceLevel(true));
    }


    public function testRespectsIndent1()
    {
        $logger = new OutputLogger;
        $logger->indentIncrease();
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . '    And it was written...$#m');
        $logger->error('And it was written...');
    }


    public function testRespectsIndent2()
    {
        $logger = new OutputLogger;
        $logger->indentIncrease();
        $logger->indentIncrease();
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . '        And it was written...$#m');
        $logger->error('And it was written...');
    }


    public function testRespectsIndent3()
    {
        $logger = new OutputLogger;
        $logger->indentIncrease();
        $logger->indentIncrease();
        $logger->indentDecrease();
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . '    And it was written...$#m');
        $logger->error('And it was written...');
    }


    public function testRespectsIndent4()
    {
        $logger = new OutputLogger;
        $logger->indentIncrease();
        $logger->indentDecrease();
        $logger->indentDecrease();
        $this->expectOutputRegex('#^' . $this->logPcrePrefix . 'And it was written...$#m');
        $logger->error('And it was written...');
    }


    public function testCanProduceBacktrace()
    {
        $logger = new OutputLogger;

        ob_start();
        $logger->setMaxBacktraceLevel(LogLevel::ERROR);
        $logger->warning('This should have no backtrace');

        $logger->setMaxBacktraceLevel(LogLevel::WARNING);
        $logger->warning('This should have a backtrace');

        $output = ob_get_clean();

        $this->assertRegExp('#^.*\[warning\] This should have no backtrace\n.*\[warning\] This should have a backtrace\n\s+In.*\n\s+1.*#m', $output);
    }


    public function testCanPassExceptionBacktrace()
    {
        $logger = new OutputLogger;

        ob_start();


        function thisFunctionThrowsAnException()
        {
            throw new \Exception('Exception message');
        }


        function thisFunctionCallsTheFunctionThatThrowsAnException()
        {
            thisFunctionThrowsAnException();
        }


        try {
            thisFunctionCallsTheFunctionThatThrowsAnException();
        } catch (\Exception $ex) {
            $logger->error('An exception occured: ' . $ex->getMessage(), array(
                'exception' => $ex
            ));
        }

        $output = ob_get_clean();

        $this->assertRegExp('#^.*\[error] An exception occured: Exception message\n\s+In.*\n\s+1.*#m', $output);
    }


    public function testCanBacktraceGlobalFunction()
    {
        $logger = new OutputLogger;


        function thisIsMyGlobalFunction($logger)
        {
            $logger->critical('Critical error test');
        }


        ob_start();
        thisIsMyGlobalFunction($logger);
        $output = ob_get_clean();

        $this->assertRegExp('#^.*\[critical\] Critical error test\n\s+In.*\n\s+1. .+thisIsMyGlobalFunction#m', $output);

        $logger->setMaxBacktraceLevel(LogLevel::ERROR);
    }
}
