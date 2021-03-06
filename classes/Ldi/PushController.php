<?php

/**
 * This class implements a pubsubhubbub publisher and subscriber
 */
class Ldi_PushController extends Ldi_Controller
{

    private $_callbackUrl;
    private $_defaultHubUrl;

    public function __construct ($app)
    {
        parent::__construct($app);

        $bootstrap = $this->_app->getBootstrap();
        $config = $bootstrap->getResource('config');

        $this->_defaultHubUrl = $config['push.hub'];
    }

    /**
     * This ist the publish method, which is called internally if a feed has been changed
     * This method implements section 7.1 of the pubsubhubbub spec:
     *  http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html#anchor9
     */
    public function publish ($topicUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');

        $postData = array(
            'hub.mode' => 'publish',
            'hub.url' => urlencode($topicUri)
        );

        $postString = '';
        foreach ($postData as $key => $value) {
            $postString .= $key . '=' . $value . '&';
        }
        rtrim($postString, '&');

        $curlHandler = curl_init();

        //set the url
        curl_setopt($curlHandler, CURLOPT_URL, $this->_defaultHubUrl);
        curl_setopt($curlHandler, CURLOPT_POST, true);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlHandler);
        $httpCode = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);

        curl_close($curlHandler);

        $logger->info('push publish: hub: ' . $this->_defaultHubUrl . ', topic: ' . $topicUri . ', return code: ' . $httpCode . ', result: ' . $result);

        if ($httpCode-($httpCode%100) != 200) {
            throw new Exception('Publishing to hub failed');
        }
    }

    public function publishFeedAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $topicUri = $request->getValue('uri', 'get');

        if ($topicUri != null) {
            $this->publish($topicUri);

            // TODO add something to the layout
        } else {
            $template->addContent('templates/publishfeed.phtml');
        }

        return $template;
    }

    public function getDefaultHubUrl ()
    {
        return $this->_defaultHubUrl;
    }
}
