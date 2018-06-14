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
     * - default WARNING (debug, info and notice are ignored)
     *
     * @var integer
     */
    private $maxLogLevel = 4;

    /**
     * Anything above this value will not provide a backtrace (unless an exception "explicitly" passed)
     * - default ERROR (error, critical, alert and emergency provide backtraces)
     *
     * @var integer
     */
    private $maxBacktraceLevel = 3;

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
     * Set the maximum log level that will provide a backtrace.
     *
     * @param string|integer $level The maximum log level. Can be passed as string or integer.
     *
     * @return void
     */
    public function setMaxBacktraceLevel($level) : void
    {
        $this->maxBacktraceLevel = self::numericLogLevel($level);
    }


    /**
     * Get the maximum log level that will provide a backtrace.
     *
     * @param boolean $asInt If true the log level is returned as an integer.
     *
     * @return string|integer The maximum log level.
     */
    public function getMaxBacktraceLevel(bool $asInt = false)
    {
        if ($asInt === false) {
            return self::stringLogLevel($this->maxBacktraceLevel);
        }
        return $this->maxBacktraceLevel;
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
     * Pass an exception in the $context array to provide a debug backtrace.
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
        $logPrefix = $logTime . $logIdent . $logLevelString . ' ' . $logIndent;
        $newLineIndent = "\n" . str_repeat(' ', strlen($logPrefix));

        $logString = str_replace("\n", $newLineIndent, trim($message));

        if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
            $file = $context['exception']->getFile();
            $line = $context['exception']->getLine();
            if ($file && $line) {
                $logString .= $newLineIndent . 'In ' . $file . ':' . $line;
            } else {
                $logString .= $newLineIndent . 'In unknown file and line'; // @codeCoverageIgnore
            }
            $logString .= $newLineIndent . str_replace("\n", $newLineIndent, self::logTraceString($context['exception']->getTrace()));
        } else if ($levelInt <= $this->getMaxBacktraceLevel(true)) {
            $trace = self::getLogTraceArray();
            $first = array_shift($trace);
            if (isset($first['file']) && isset($first['line'])) {
                $logString .= $newLineIndent . 'In ' . $first['file'] . ':' . $first['line'];
            } else {
                $logString .= $newLineIndent . 'In unknown file and line'; // @codeCoverageIgnore
            }
            $logString .= $newLineIndent . str_replace("\n", $newLineIndent, self::logTraceString($trace));
        }

        $output = $logPrefix . $logString . "\n";

        if (is_string($this->logFilename)) {
            file_put_contents($this->logFilename, $output, FILE_APPEND);
        }
        if ($this->output) {
            echo $output;
        }
    }


    /**
     * Get a default debug backtrace with functions belonging to this class removed.
     *
     * @return array
     */
    public static function getLogTraceArray() : array
    {
        $trace = debug_backtrace();

        $retval = array();

        $leftLoggerClass = false;

        foreach ($trace as $entryId => $entry) {
            if (empty($entry['class']) || $entry['class'] !== __CLASS__) {
                $leftLoggerClass = true;
            }
            if (!$leftLoggerClass) {
                if (isset($trace[$entryId - 1])) {
                    unset($trace[$entryId - 1]);
                }
            }
        }
        return array_values($trace);
    }


    /**
     * Convert a backtrace array to a string.
     *
     * @param array   $trace     The backtrace array - for example from debug_backtrace.
     * @param integer $maxLength The maximum number of "steps" to return.
     *
     * @return string
     */
    public static function logTraceString(array $trace, int $maxLength = 6) : string
    {
        $retval = array();
        foreach ($trace as $entryId => $entry) {
            $function = $entry['function'];
            if (isset($entry['class']) && !empty($entry['class'])) {
                $function = $entry['class'] . (isset($entry['type']) ? $entry['type'] : '->') . $function;
            }

            if (isset($entry['args']) && !empty($entry['args'])) {
                $args = array();
                foreach ($entry['args'] as $arg) {
                    $args[] = gettype($arg);
                }
                $function .= '(' . implode(', ', $args) . ')';
            } else {
                $function .= '()';
            }

            $position = 'filename and line unknown';
            if (isset($entry['file'])) {
                $position = $entry['file'];
            }
            if (isset($entry['line'])) {
                $position .= ':' . $entry['line'];
            }

            $retval[] = sprintf('%3s. %s - %s', $entryId + 1, $function, $position);

            if (count($retval) >= $maxLength) {
                $omitted = (count($trace) - $maxLength);
                if ($omitted > 0) {
                    $retval[] = '(...' . $omitted . ' more omitted...)';
                }
                break;
            }
        }
        return implode("\n", $retval);
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
