<?php

class Spb_AnnotateController extends Spb_Controller
{

    /**
     * Returns a Feed in the spezified format (html, rss, atom)
     */
    public function addNote($resourceUri = null, $source = null, $note = null)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $store = $bootstrap->getResource('store');
        $logger = $bootstrap->getResource('logger');

        $logger->info('addNote Action called resource: "' . $resourceUri . '", source: "' . $source . '", note: "' . var_export($note, true) . '".');

        $nsPingback = 'http://purl.org/net/pingback/';
        $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $nsSioc = 'http://rdfs.org/sioc/ns#';
        $nsAtom = 'http://www.w3.org/2005/Atom/';
        $nsXsd = 'http://www.w3.org/2001/XMLSchema#';

        // TODO use some other identification
        $noteUri = 'http://localhost/' . md5(rand()) . '/';
        $now = date('c');

        $newNote = array(
            $resourceUri => array(
                $nsPingback . 'ping' => array(
                    array(
                        'type' => 'uri',
                        'value' => $noteUri
                    )
                )
            ),
            $noteUri => array(
                $nsRdf . 'type' => array(
                    array(
                        'type' => 'uri',
                        'value' => $nsPingback . 'Item'
                    )
                ),
                $nsAtom . 'published' => array(
                    array(
                        'type' => 'literal',
                        'value' => $now,
                        'datatype' => $nsXsd . 'dateTime'
                    )
                ),
                $nsPingback . 'source' => array(
                    array(
                        'type' => 'uri',
                        'value' => $source
                    )
                ),
                $nsPingback . 'target' => array(
                    array(
                        'type' => 'uri',
                        'value' => $resourceUri
                    )
                ),
                $nsPingback . 'changeset' => array(
                    array(
                        'type' => 'literal',
                        'value' => var_export($note, true)
                    )
                )
            )
        );
        $store->addMultipleStatements($model->getModelIri(), $newNote);
        $logger->info('addNote: note written to model.');

        $feedUri = $this->_app->getBaseUri() . '?c=feed&a=getfeed&uri=' . urlencode($resourceUri);

        $logger->info('publish updates to feed: "' . $feedUri . '".');

        // TODO tell the Push Controller to publish this resources feed
        $pushController = new Spb_PushController($this->_app);
        $pushController->publish($feedUri);
    }

    public function getNotes($resourceUri = null)
    {
        // There are two namespaces, one is used in atom files the other one for RDF
        $nsAairAtom = 'http://activitystrea.ms/schema/1.0/';
        $nsAair = 'http://xmlns.notu.be/aair#';
        $nsPingback = 'http://purl.org/net/pingback/';
        $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $nsSioc = 'http://rdfs.org/sioc/ns#';

        $model = $this->_app->getBootstrap()->getResource('model');

        if ($resourceUri === null) {
            return null;
        }

        // TODO query the activities, which are added in the upper method
        $query = '' .
            'PREFIX atom: <http://www.w3.org/2005/Atom/> ' .
            'PREFIX aair: <' . $nsAair . '> ' .
            'PREFIX pingback: <' . $nsPingback . '> ' .
            'SELECT ?ping ?pubDate ?source ?target ?changeset ' .
            'WHERE { ' .
            '   <' . $resourceUri . '> pingback:ping ?ping . ' .
            '   ?ping a pingback:Item ; ' .
            '         pingback:source ?source ; ' .
            '         pingback:target ?target ; ' .
            '         pingback:changeset ?changeset ; ' .
            '         atom:published ?pubDate. ' .
            '} ' .
            'ORDER BY DESC(?pubDate)';
        $pingsResult = $model->sparqlQuery($query);

        $activities = array();

        foreach ($pingsResult as $ping) {
            $pingUri = $ping['ping'];
            $source = $ping['source'];
            $target = $ping['target'];
            $changeset = $ping['changeset'];
            $pubDate = $ping['pubDate'];

            $pubDate = self::_issueE24fix($pubDate);

            $activity = array(
                'title' => 'Comment from "' . $source . '".',
                'uri' => $pingUri,
                'author' => $source,
                'authorUri' => $source,
                'pubDate' => $pubDate,
                'comment' => $changeset,
            );

            $activities[] = $activity;
        }

        return $activities;
    }

    /**
     * Quick fix for Erfurt issue #24 (https://github.com/AKSW/Erfurt/issues/24)
     */
    private static function _issueE24fix ($date)
    {
        if (strstr($date, 11, 1) != 'T') {
            $dateObj = date_create($date);
            return date_format($dateObj, 'c');
        } else {
            return $date;
        }
    }
}
