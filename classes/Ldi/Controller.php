<?php

class Ldi_Controller
{
    protected $_app = null;

    public function __construct ($app) {
        $this->_app = $app;
    }
}
