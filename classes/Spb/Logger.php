<?php

class Spb_Logger
{

    private $_file;

    public function __construct ($filePath = null)
    {
        $app = Application::getInstance();

        if ($filePath === null) {
            $filePath = $app->getBaseDir() . '/spb.log';
        }

        $this->_file = fopen($filePath, 'a');
    }

    public function __destruct ()
    {
        fclose($this->_file);
    }

    public function info ($message) {
        fwrite($this->_file, time() . ' - ' . $message . "\n");
    }
}
