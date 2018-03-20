<?php
namespace Mikk3lRo\atomix\io;

use Mikk3lRo\atomix\system\DirConf;

class Logger {
    /**
     * Keeps track of the current indent level
     * 
     * @var int
     */
    static private $_indent = 0;
    
    /**
     * Anything above this value will be ignored
     * 
     * @var int
     */
    static private $_max_log_level = 4;
    
    /**
     * Write log to cli / browser?
     * 
     * @var bool
     */
    static private $_output = true;
    
    /**
     * The absolute path to the file we want to write to - or false for none
     * 
     * @var string 
     */
    static private $_log_filename = false;
    
    /**
     * Set the log filename
     * 
     * @param mixed $filename A relative (to `DirConf::get('log')`) filename, or an absolute filename, or false to disable logging to file completely.
     */
    static public function set_log_filename($filename) {
        if (!is_string($filename)) {
            self::$_log_filename = false;
        } else if (substr($filename, 0, 1) !== '/') {
            self::$_log_filename = DirConf::get('log') . '/' . $filename;
        } else {
            self::$_log_filename = $filename;
        }
    }
    
    /**
     * Get the log filename
     */
    static public function get_log_filename() {
        return self::$_log_filename;
    }
    
    /**
     * Sets the maximum log level that will be written / output by this class
     * 
     * @param int $level Default is 4 (which should be very verbose)
     */
    static public function set_max_log_level($level) {
        self::$_max_log_level = intval($level);
    }
    
    /**
     * Get the maximum log level that will be written or output by this class
     * 
     * @return int
     */
    static public function get_max_log_level() {
        return self::$_max_log_level;
    }

        static public function set_output(bool $output) {
        self::$_output = $output;
    }
    
    /**
     * Appends to a flat logfile (if enabled), and outputs to browser / cli (if enabled)
     *  
     * @param mixed     $string_or_array    What to write
     * @param int       $log_level          Higher levels are hidden when output verbosity is reduced
     */
    static function write($what, $log_level = 0) {
        if (self::$_max_log_level < $log_level) {
            //Ignore it completely
            return;
        }
        
        //Convert anything that is not a string
        if (!is_string($what)) {
            $what = var_export($what, true);
        }
        
        $log_time = '[' . date('Y-m-d H:i:s') . ']';
        $log_ident = '[' . str_pad(getmypid(), 6, ' ', STR_PAD_LEFT) . ']';
        $log_indent = self::indent();
        $log_string = str_replace("\n", "\n" . $log_indent . str_repeat(' ', 30), trim($what));

        $output = $log_time . $log_ident . ' ' . $log_indent . $log_string . "\n";
        
        if (is_string(self::$_log_filename)) {
            file_put_contents(self::$_log_filename, $output, FILE_APPEND);
        }
        if (self::$_output) {
            echo $output;
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
}