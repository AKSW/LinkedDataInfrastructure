<?php

class Ldi_IndexController extends Ldi_Controller
{
    public function indexAction ($template) {
        $ret = $this->rdfSignatureIfAccepted($template);
        if ($ret !== null) {
            return $ret;
        }
        $template->addContent('templates/welcome.phtml');
        return $template;
    }
}
