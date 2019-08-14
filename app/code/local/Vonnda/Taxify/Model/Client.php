<?php

class Vonnda_Taxify_Model_Client extends Mage_Core_Model_Abstract
{

    public $config;
    public $request = array();
    public $response;
    public $url = '';
    public $soapClient;
    public $logFilename = 'taxify.log';
    const PARTNER_KEY = '275067E9-C359-4BF3-AC6E-2772456F6FAD';

    public function __construct()
    {
        $this->config = Mage::getModel('taxify/config');
        $this->url = $this->config->getApiUrl(). '?wsdl';
        $this->log('URL:'. $this->url);

        $this->soapClient = new SoapClient($this->url, array(
            'trace' => 1,
            'exception' => 0,
        ));
    }

    public function log($message)
    {
        Mage::log($message, null, $this->logFilename);
    }

    public function addSecurityToRequest()
    {
        $this->request['Request']['Security'] = array(
            'Username' => $this->config->getApiUsername(),
            'Password' => $this->config->getApiPassword(),
        );
        $this->request['Request']['Security']['PartnerKey'] = self::PARTNER_KEY;
    }

    public function buildRequest($req)
    {
        $this->request['Request'] = $req;
        $this->addSecurityToRequest();
    }

    public function logTransaction()
    {
        $this->log('REQUEST:');
        $this->log($this->request);
        $this->log('RESPONSE:');
        $this->log($this->response);
    }

    public function request($method, $req)
    {
        $this->buildRequest($req);

        try {
            $this->response = $this->soapClient->$method($this->request);
        } catch (Exception $e) {
            $this->log('ERROR:');
            $this->log($e->getMessage());
        }

        $this->logTransaction();

        return $this->response;
    }
}
