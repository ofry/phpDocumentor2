<?php
abstract class DocBlox_Abstract
{
  const VERSION = '0.5.0';
  
  static protected $logger       = null;
  static protected $debug_logger = null;
  static protected $log_level    = Zend_Log::WARN;

  protected $timer = array();

  public function __construct()
  {
    $this->resetTimer();
  }

  protected function resetTimer($name = 'default')
  {
    $this->timer[$name] = microtime(true);
  }

  protected function getElapsedTime($name = 'default')
  {
    return microtime(true) - $this->timer[$name];
  }

  public function setLogLevel($level)
  {
    self::$log_level = $level;
  }

  protected function debugTimer($message, $name = 'default')
  {
    $this->debug($message.' in '.number_format($this->getElapsedTime($name), 4).' seconds');
    $this->resetTimer($name);
  }

  protected function debug($message)
  {
    // is the log level is below debugging; just skip this
    if (self::$log_level < Zend_Log::DEBUG)
    {
      return;
    }

    if (!self::$debug_logger)
    {
      // TODO convert to loading from config
      self::$debug_logger = new Zend_Log(new Zend_Log_Writer_Stream(fopen('log/'.date('YmdHis').'.debug.log', 'w')));
    }

    $dump = $message;

    if (!is_string($dump))
    {
      ob_start();
      var_dump($message);
      $dump = ob_get_clean();
    }

    self::$debug_logger->log($dump, Zend_Log::DEBUG);
  }

  public function log($message, $priority = Zend_Log::INFO)
  {
    // is the log level is below the priority; just skip this
    if (self::$log_level < $priority)
    {
      return;
    }

    if ($priority == Zend_Log::DEBUG)
    {
      $this->debug($message);
      return;
    }

    if (!self::$logger)
    {
      // TODO convert to loading from config
      self::$logger = new Zend_Log(new Zend_Log_Writer_Stream(fopen('log/'.date('YmdHis').'.errors.log', 'w')));
    }

    static $priority_names = null;
    if ($priority_names === null)
    {
      $r = new ReflectionClass('Zend_Log');
      $priority_names = array_flip($r->getConstants());
    }

    echo '['.$priority_names[$priority].': '.date('H:i').']: '.$message.PHP_EOL;
    self::$logger->log($message, $priority);
  }

}