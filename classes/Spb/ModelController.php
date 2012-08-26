<?php
require_once 'Tools.php';

class Spb_ModelController extends Spb_Controller
{
    public static $SPO_QUERY = 'SELECT ?s ?p ?o WHERE {?s ?p ?o.}';

    public function getChanges ($resourceUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $store = $bootstrap->getResource('store');

        if ($store->isModelAvailable($resourceUri, true)) {
            $model = $store->getModel($resourceUri);
        } else {
            $model = $store->getNewModel ($resourceUri);
        }
        $result = $model->sparqlQuery(self::$SPO_QUERY);

        $localModel = new Erfurt_Rdf_MemoryModel($result);
        $localStatements = $localModel->getStatements();

        $currentStatements = Tools::getLinkedDataResource($resourceUri, $model->getModelIri());
        $diffStatements = Erfurt_Rdf_Model::getStatementsDiff($localStatements, $currentStatements);

        return $diffStatements;
    }

    public function diffAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $resourceUri = $request->getValue('resource', 'get');

        if ($resourceUri !== null) {
            $diff = $this->getChanges($resourceUri);
            var_dump($diff);
        } else {
            $template->addContent('templates/resourcediff.phtml');
        }

        return $template;
    }

    public function exportAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $model = $bootstrap->getResource('model');

        $format = $request->getValue('format', 'get');

        $filename = 'export' . date('Y-m-d_Hi');

        switch ($format) {
            case 'rdfxml':
                $contentType = 'application/rdf+xml';
                $filename .= '.rdf';
                break;
            case 'rdfn3':
                $contentType = 'text/rdf+n3';
                $filename .= '.n3';
                break;
            case 'rdfjson':
                $contentType = 'application/json';
                $filename .= '.json';
                break;
            case 'turtle':
                $contentType = 'application/x-turtle';
                $filename .= '.ttl';
                break;
            default:
                $contentType = 'application/x-turtle';
                $format = 'turtle';
                $filename .= '.ttl';
        }

        $modelUri = $model->getModelIri();
        $format = Erfurt_Syntax_RdfSerializer::normalizeFormat($format);
        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);

        $rdfData = $serializer->serializeGraphToString($modelUri);
        header('Content-type: ' . $contentType);
        header('Content-Disposition', ('filename="' . $filename . '"'));

        $template->disableLayout();
        $template->setRawContent($rdfData);

        return $template;
    }

    public function queryAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $model = $bootstrap->getResource('model');

        $query = $request->getValue('query', 'post');

        if ($query !== null) {
            $result = $model->sparqlQuery($query);

            $template->addContent('templates/queryresult.phtml');
            $template->result = $result;
        } else {
            $template->addContent('templates/query.phtml');
        }

        return $template;
    }
}
