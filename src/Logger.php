<?php
namespace Mikk3lRo\atomix\io;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Mikk3lRo\atomix\system\DirConf;
use Mikk3lRo\atomix\io\Formatters;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Keeps track of the current indent level
     *
     * @var integer
     */
    private $indent = 0;

    /**
     * Anything above this value will be ignored
     * - default WARNING (ie. debug, info, notice are ignored)
     *
     * @var integer
     */
    private $maxLogLevel = 4;

    /**
     * Write log to cli / browser?
     *
     * @var boolean
     */
    private $output = true;

    /**
     * The absolute path to the file we want to write to - or false for none.
     *
     * @var string
     */
    private $logFilename = false;


    /**
     * Set the log filename
     *
     * @param string $filename A relative (to `DirConf::get('log')`) filename, or an absolute filename, or null to disable logging to file completely.
     *
     * @return void
     */
    public function setLogFilename(?string $filename) : void
    {
        if (!is_string($filename)) {
            $this->logFilename = null;
        } else if (substr($filename, 0, 1) !== '/') {
            $this->logFilename = DirConf::get('log') . '/' . $filename;
        } else {
            $this->logFilename = $filename;
        }
    }


    /**
     * Get the log filename
     *
     * @return string|null Returns the log filename or null if not logging to a file
     */
    public function getLogFilename() : ?string
    {
        return $this->logFilename;
    }


    /**
     * Set the maximum log level that will be written / output by this class
     *
     * @param string|integer $level The maximum log level. Can be passed as string or integer.
     *
     * @return void
     */
    public function setMaxLogLevel($level) : void
    {
        $this->maxLogLevel = self::numericLogLevel($level);
    }


    /**
     * Get the maximum log level that will be written or output by this class
     *
     * @param boolean $asInt If true the log level is returned as an integer.
     *
     * @return string|integer The maximum log level.
     */
    public function getMaxLogLevel(bool $asInt = false)
    {
        if ($asInt === false) {
            return self::stringLogLevel($this->maxLogLevel);
        }
        return $this->maxLogLevel;
    }


    /**
     * Disable output to browser / cli.
     *
     * @return void
     */
    public function disableOutput() : void
    {
        $this->output = false;
    }


    /**
     * Enable output to browser / cli.
     *
     * @return void
     */
    public function enableOutput() : void
    {
        $this->output = true;
    }


    /**
     * Convert a log level in string format to a number.
     *
     * @param string|integer $loglevel The log level as string or integer.
     *
     * @return integer The log level as integer.
     */
    private static function numericLogLevel($loglevel) : int
    {
        if (is_int($loglevel)) {
            return $loglevel;
        }
        switch ($loglevel) {
            case LogLevel::EMERGENCY:
                return 0;
            case LogLevel::ALERT:
                return 1;
            case LogLevel::CRITICAL:
                return 2;
            case LogLevel::ERROR:
                return 3;
            case LogLevel::WARNING:
                return 4;
            case LogLevel::NOTICE:
                return 5;
            case LogLevel::INFO:
                return 6;
            case LogLevel::DEBUG:
            default:
                return 7;
        }
    }


    /**
     * Convert a log level in integer format to a string.
     *
     * @param integer $loglevel The log level as integer.
     *
     * @return string The log level as string.
     */
    private static function stringLogLevel(int $loglevel) : string
    {
        switch ($loglevel) {
            case 0:
                return LogLevel::EMERGENCY;
            case 1:
                return LogLevel::ALERT;
            case 2:
                return LogLevel::CRITICAL;
            case 3:
                return LogLevel::ERROR;
            case 4:
                return LogLevel::WARNING;
            case 5:
                return LogLevel::NOTICE;
            case 6:
                return LogLevel::INFO;
            case 7:
            default:
                return LogLevel::DEBUG;
        }
    }


    /**
     * Appends to a flat file (if enabled), and outputs to browser / cli (if enabled)
     *
     * @param string|integer $level   One of the constants from Psr\Log\LogLevel or the equivalent integer value.
     * @param string|mixed   $message What to log - normally a string, though anything castable to a string will work.
     * @param array          $context An array of substitutions to make in the passed string.
     *
     * @return void
     */
    public function log($level, $message, array $context = array()) : void
    {
        $levelInt = self::numericLogLevel($level);
        if ($this->maxLogLevel < $levelInt) {
            //Ignore it completely
            return;
        }

        //Convert anything that is not a string
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        //Replace any tags in the message with values from context
        if (!empty($context)) {
            $message = Formatters::replaceTags($message, $context);
        }

        //Prepend the date and time, pid, log level and indent.
        $logTime = '[' . date('Y-m-d H:i:s') . ']';
        $logIdent = str_pad('[' . getmypid() . ']', 8, ' ', STR_PAD_LEFT);
        $logLevelString = str_pad('[' . $level . ']', 11, ' ', STR_PAD_LEFT);
        $logIndent = $this->indent();
        $logString = str_replace("\n", "\n" . $logIndent . str_repeat(' ', 41), trim($message));

        $output = $logTime . $logIdent . $logLevelString . ' ' . $logIndent . $logString . "\n";

        if (is_string($this->logFilename)) {
            file_put_contents($this->logFilename, $output, FILE_APPEND);
        }
        if ($this->output) {
            echo $output;
        }
    }


    /**
     * Increases the log indentation by 4 spaces.
     *
     * @return void
     */
    public function indentIncrease() : void
    {
        $this->indent += 4;
    }

    
    /**
     * Decreases the log indentation by 4 spaces.
     *
     * @return void
     */
    public function indentDecrease() : void
    {
        $this->indent -= 4;
    }


    /**
     * Gets the current log indent as a string - ready to prepend each line.
     *
     * @return string
     */
    private function indent() : string
    {
        return str_repeat(' ', $this->indent);
    }
}
