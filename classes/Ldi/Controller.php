<?php

class Ldi_Controller
{
    private static $DELIVERABLE_TYPES = array(
        'text/html'             => array('format' => 'html'),
        'text/turtle'           => array('format' => 'rdf', 'serialization' => 'turtle'),
        'application/x-turtle'  => array('format' => 'rdf', 'serialization' => 'turtle'),
        'text/n3'               => array('format' => 'rdf', 'serialization' => 'turtle')
    );

    protected $_app = null;

    public function __construct ($app)
    {
        $this->_app = $app;
    }

    /**
     * This method replies the signature of the controller of an action called on the controller in
     * a RDF format accepted by the client
     */
    public function signatureAction ($template, $format = null)
    {
        $rdf = $this->getSignature($format);
        header('Content-Type: application/x-turtle');

        $template->disableLayout();
        $template->setRawContent($rdf);

        return $template;
    }

    /**
     * This method returns the signature of the controller in the given rdf format
     */
    protected function getSignature ($format = null)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $rdf = '@prefix pingback: <http://purl.org/net/pingback/> . ' . PHP_EOL
            . '<' . $request->getRequestUri() . '> '
            . 'pingback:to '
            . '<' . $this->_app->getBaseUri() . '?c=pingback&a=ping>.' . PHP_EOL;

        return $rdf;
    }

    /**
     * This method sends a RDF response which describes signature of the called action and/or
     * controller if the accept header includes any RDF format.
     * This method is meant to be called at the beginning of any action to provide a generic RDF
     * response.
     */
    public function rdfSignatureIfAccepted ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $header = $request->getHeader();

        $accept = $this->parseAcceptString($header['Accept']);

        switch ($accept['format']) {
            case 'rdf':
                return $this->signatureAction($template, $accept['serialization']);
            case 'html':
            default:
                return null;
        }

        return null;
    }

    private static function parseAcceptString ($acceptString, $deliverableTypes = null)
    {
        if ($deliverableTypes === null) {
            $deliverableTypes = self::$DELIVERABLE_TYPES;
        }

        $acceptArray = explode(',', $acceptString);

        foreach ($acceptArray as $accept) {
            if (isset($deliverableTypes[$accept])) {
                return $deliverableTypes[$accept];
            }
        }

        return array_values($deliverableTypes)[0];
    }
}
