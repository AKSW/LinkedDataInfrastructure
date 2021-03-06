<?php

class Ldi_FeedController extends Ldi_Controller
{

    /**
     * Returns a Feed in the spezified format (html, rss, atom)
     */
    public function getFeedAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $request = $bootstrap->getResource('request');

        $uri = $request->getValue('uri');

        if ($uri !== null) {

            $annotateController = $this->_app->getController('Ldi_AnnotateController');
            $notes = $annotateController->getNotes($uri);

            $pushController = $this->_app->getController('Ldi_PushController');

            $feedUri = $this->_app->getBaseUri() . '?c=feed&amp;a=getfeed&amp;uri=' . urlencode($uri);

            $updated = '0';

            foreach ($notes as $note) {
                if (0 > strcmp($updated, $note['pubDate'])) {
                    $updated = $note['pubDate'];
                }
            }

            $template->setLayout('templates/feed.phtml');
            $template->updated = $updated;
            $template->uri = $uri;
            $template->feedUri = $feedUri;
            $template->hub = $pushController->getDefaultHubUrl();
            $template->name = $uri;
            $template->comments = $notes;
        } else {
            // No URI given
            $template->addContent('templates/getfeed.phtml');
            //throw new Exception('No URI given for feed');
        }

        return $template;
    }

}
