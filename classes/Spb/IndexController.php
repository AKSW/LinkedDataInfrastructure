<?php

class Spb_IndexController extends Spb_Controller
{
    public function indexAction ($template) {
        $template->addContent('templates/welcome.phtml');
        return $template;
    }
}
