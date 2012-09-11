<?php

class Ldi_IndexController extends Ldi_Controller
{
    public function indexAction ($template) {
        $template->addContent('templates/welcome.phtml');
        return $template;
    }
}
