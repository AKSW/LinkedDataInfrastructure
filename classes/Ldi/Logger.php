<?php

class Ldi_Logger
{

    private $_file;

    public function __construct ($filePath = null)
    {
        $app = Application::getInstance();

        if ($filePath === null) {
            $filePath = $app->getBaseDir() . 'ldi.log';
        }

        if (! $this->_file = @fopen($filePath, 'a')) {
            throw new Exception(
                'Could not open the log file. Please create "ldi.log" in ldi-root and add ' .
                'write-permissions for the webserver.'
            );
        }
    }

    public function __destruct ()
    {
        fclose($this->_file);
    }

    public function info ($message) {
        fwrite($this->_file, time() . ' - ' . $message . "\n");
    }
}
