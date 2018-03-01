<?php
namespace Mikk3lRo\atomix\io;

class Logger {
    static private $_log_filename = null;
    static private $_indent = 0;
    /**
     * Get the current script ID / log name
     * 
     * @return string
     */
    static private function get_log_name() {
        if (defined('LOG_FILENAME')) {
            self::$_log_filename = LOG_FILENAME;
        }
        if (!is_string(self::$_log_filename)) {
            //Find the "originating" script - the "topmost" php-file
            $backtrace = debug_backtrace(
                defined("DEBUG_BACKTRACE_IGNORE_ARGS")
                ? DEBUG_BACKTRACE_IGNORE_ARGS
                : FALSE);
            $top_frame = array_pop($backtrace);
            self::$_log_filename = isset($top_frame['file']) ? basename($top_frame['file']) : 'unknown';
        }
        return self::$_log_filename;
    }

    /**
     * Appends to a flat logfile, as well as the browser or cli if DEBUG is true.
     * The special log_name 'output' skips the log file and only outputs to the
     * browser or cli.
     *  
     * @param mixed     $string_or_array    What to write
     * @param int       $log_level          Higher levels are hidden when output verbosity is reduced
     */
    static function write($string_or_array, $log_level = 0) {
        if (DEBUG_LEVEL < $log_level) {
            return;
        }
        if (!is_string($string_or_array)) {
            $string_or_array = var_export($string_or_array, true);
        }
        $log_indent = self::indent();
        $string_or_array = '[' . date('Y-m-d H:i:s') . '][' . str_pad(getmypid(), 6, ' ', STR_PAD_LEFT) . '] ' . $log_indent . str_replace("\n", "\n" . $log_indent . str_repeat(' ', 30), trim($string_or_array));
        $log_name = self::get_log_name();
        if ($log_name === 'output' || !is_string($log_name)) {
            echo $string_or_array . "\n";
        } else {
            $logfile = DIR_LOGS . '/' . $log_name . '.log';
            if (!is_dir(DIR_LOGS)) {
                mkdir(dirname($logfile), 0700, true);
            }
            file_put_contents($logfile, $string_or_array . "\n", FILE_APPEND);
        }
    }

    /**
     * Increases the log indentation by 4 spaces.
     */
    static function indent_increase() {
        self::$_indent += 4;
    }

    /**
     * Decreases the log indentation by 4 spaces.
     * 
     * @global int $log_indent
     */
    static function indent_decrease() {
        self::$_indent -= 4;
    }

    /**
     * Gets the current log indent as a string - ready to prepend each line.
     * 
     * @return string
     */
    static private function indent() {
        return str_repeat(' ', self::$_indent);
    }
    
    static function enable_errors() {
        ini_set('display_errors', 1);
    }
    static function disable_errors() {
        ini_set('display_errors', 0);
    }
}