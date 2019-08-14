<?php

class Vonnda_Taxify_Model_Request_Codes extends Vonnda_Taxify_Model_Request_Request
{
    public $apiMethod = 'GetCodes';
    public $defaultCodes = array(
        '',
        'CLOTHING',
        'FOOD',
        'FREIGHT',
        'NONTAX',
        'TAXABLE',
    );

    public function build()
    {
        $this->request['GetCodes'] = array('CodeType' => 'Item');
    }

    public function getCodes()
    {
        // Return defaults if something goes wrong
        if (!isset($this->response->GetCodesResult->Codes->string)) {
            return $this->defaultCodes;
        }

        if (!is_array($this->response->GetCodesResult->Codes->string)) {
            return $this->defaultCodes;
        }

        $result = $this->response->GetCodesResult->Codes->string;
        array_unshift($result, ''); // add blank which is default tax

        return $result;
    }

    public function getCodesWithLabels()
    {
        $codesWithLabels = array();
        $codes = $this->getCodes();
        foreach ($codes as $code) {
            $codesWithLabels[$code] = $this->getLabelFromCode($code);
        }

        return $codesWithLabels;
    }

    public function getLabelFromCode($code)
    {
        if ($code == '') {
            return 'Default';
        }

        return ucwords(strtolower($code));
    }

}
