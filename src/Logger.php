<?php
namespace Mikk3lRo\atomix\io;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Mikk3lRo\atomix\system\DirConf;
use Mikk3lRo\atomix\io\Formatters;

class Logger implements LoggerInterface {
    use LoggerTrait;

    /**
     * Keeps track of the current indent level
     *
     * @var int
     */
    private $_indent = 0;

    /**
     * Anything above this value will be ignored
     * - default WARNING (ie. debug, info, notice are ignored)
     *
     * @var int
     */
    private $_max_log_level = 4;

    /**
     * Write log to cli / browser?
     *
     * @var bool
     */
    private $_output = true;

    /**
     * The absolute path to the file we want to write to - or false for none
     *
     * @var string
     */
    private $_log_filename = false;

    /**
     * Set the log filename
     *
     * @param mixed $filename A relative (to `DirConf::get('log')`) filename, or an absolute filename, or false to disable logging to file completely.
     */
    public function set_log_filename($filename) {
        if (!is_string($filename)) {
            $this->_log_filename = false;
        } else if (substr($filename, 0, 1) !== '/') {
            $this->_log_filename = DirConf::get('log') . '/' . $filename;
        } else {
            $this->_log_filename = $filename;
        }
    }

    /**
     * Get the log filename
     */
    public function get_log_filename() {
        return $this->_log_filename;
    }

    /**
     * Sets the maximum log level that will be written / output by this class
     *
     * @param int $level Default is 4 = WARNING
     */
    public function set_max_log_level($level) {
        $this->_max_log_level = self::numericLogLevel($level);
    }

    /**
     * Get the maximum log level that will be written or output by this class
     *
     * @return int
     */
    public function get_max_log_level($as_int = false) {
        if ($as_int === false) {
            return self::stringLogLevel($this->_max_log_level);
        }
        return $this->_max_log_level;
    }

    public function set_output(bool $output) {
        $this->_output = $output;
    }

    private static function numericLogLevel($loglevel) {
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

    private static function stringLogLevel(int $loglevel) {
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
     * Appends to a flat logfile (if enabled), and outputs to browser / cli (if enabled)
     *
     * @param string $level One of the constants from Psr\Log\LogLevel
     * @param string $message What to log (normally a string)
     * @param array  $context An array of substitutions to make in the passed string
     *
     * @return void
     */
    public function log($level, $message, array $context = array()) {
        $level_int = self::numericLogLevel($level);
        if ($this->_max_log_level < $level_int) {
            //Ignore it completely
            return;
        }

        //Convert anything that is not a string
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        if (!empty($context)) {
            $message = Formatters::replaceTags($message, $context);
        }

        $log_time = '[' . date('Y-m-d H:i:s') . ']';
        $log_ident = str_pad('[' . getmypid() . ']', 8, ' ', STR_PAD_LEFT);
        $log_level_str = str_pad('[' . $level . ']', 11, ' ', STR_PAD_LEFT);
        $log_indent = $this->indent();
        $log_string = str_replace("\n", "\n" . $log_indent . str_repeat(' ', 41), trim($message));

        $output = $log_time . $log_ident . $log_level_str . ' ' . $log_indent . $log_string . "\n";

        if (is_string($this->_log_filename)) {
            file_put_contents($this->_log_filename, $output, FILE_APPEND);
        }
        if ($this->_output) {
            echo $output;
        }
    }

    /**
     * Increases the log indentation by 4 spaces.
     */
    public function indent_increase() {
        $this->_indent += 4;
    }

    /**
     * Decreases the log indentation by 4 spaces.
     *
     * @global int $log_indent
     */
    public function indent_decrease() {
        $this->_indent -= 4;
    }

    /**
     * Gets the current log indent as a string - ready to prepend each line.
     *
     * @return string
     */
    private function indent() {
        return str_repeat(' ', $this->_indent);
    }
}