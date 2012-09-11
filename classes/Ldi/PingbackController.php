<?php

require_once 'Tools.php';

class Ldi_PingbackController extends Ldi_Controller
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
            $modelUri = $model->getModelIri();
            $sourceStatements = Tools::getLinkedDataResource($source, $modelUri);

            if ($sourceStatements !== null) {
                $memModel = new Erfurt_Rdf_MemoryModel($sourceStatements);

                if ($memModel->hasResource($target)) {
                    $o = array('type' => 'uri', 'value' => $target);
                    $sp = $memModel->getSP($o);
                    $po = $memModel->getPO($target);
                    $so = $memModel->getSO($target);

                    if (count($sp) > 0 || count($po) > 0 || count($so) > 0) {
                        $spo = array();
                        $spo[$target] = $po;
                        $spo = array_merge($spo, $sp, $so);

                        $annotateController = $this->_app->getController('Ldi_AnnotateController');
                        $annotateController->addNote($target, $source, $spo);
                    } else {
                        // should not happen, because we have checked this with hasResource()
                        throw new Exception(
                            'The ping is invalid, because there are no statements about the target at '
                            . 'the source (second check)'
                        );
                    }
                } else {
                    // no statements with target found in source -> invalid ping
                    throw new Exception(
                        'The ping is invalid, because there are no statements about the target at '
                        . 'the source'
                    );
                }
            } else {
                //no statements found -> invalid ping
                throw new Exception(
                    'The ping is invalid, because there are no statements at the source'
                );
            }

            $fromform = $request->getValue('fromform', 'post');

            $successMsg = 'Ping was received successfully';

            if ($fromform !== null) {
                $template->addMessage($successMsg);
            } else {
                $template->disableLayout();
                $template->setRawContent('OK. ' . $successMsg);
            }
        } else {
            // no ping, show form for manual ping
            $template->addContent('templates/sendpingback.phtml');
        }

        return $template;
    }

}
