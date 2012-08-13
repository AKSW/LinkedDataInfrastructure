<?php

require_once 'Tools.php';

class Spb_PingbackController extends Spb_Controller
{
    public function pingAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $logger->info('pingAction called');

        $source = $request->getValue('source', 'post');
        $target = $request->getValue('target', 'post');
        $comment = $request->getValue('comment', 'post');

        $logger->info('ping: source: "' . $source . '", target: "' . $target . '", comment: "' . $comment . '".');

        if ($source !== null && $target !== null) {
            // TODO store and interprete ping
            $modelUri = $model->getModelIri();
            $sourceStatements = Tools::getLinkedDataResource($source, $modelUri);

            if ($sourceStatements !== null) {
                $memModel = new Erfurt_Rdf_MemoryModel($sourceStatements);

                $o = array('type' => 'uri', 'value' => $target);
                $spo = $memModel->getSP($o);

                // TODO get also statements, where the target occures at S or P

                $template->addDebug(var_export($spo, true));

                if (count($spo) <= 0) {
                    return;
                }

                $annotateController = new Spb_AnnotateController($this->_app);
                $annotateController->addNote($target, $source, $spo);
            }
        } else {
            // invalide ping
            $template->addContent('templates/sendpingback.phtml');
        }

        return $template;
    }

}
