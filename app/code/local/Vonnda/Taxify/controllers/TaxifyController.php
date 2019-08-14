<?php

class Vonnda_Taxify_TaxifyController extends Mage_Adminhtml_Controller_Action
{

    // Show order export page
    public function indexAction()
    {
        $this->loadLayout();
        $content = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'taxify_export', array('template' => 'taxify/export.phtml'));
        $this->getLayout()->getBlock('content')->insert($content);
        $this->renderLayout();
    }

    // Exports previous orders for people that want to migrate to taxify
    public function exportAction()
    {
        $export = Mage::getModel('taxify/export');
        $export->orderStatuses = $this->getRequest()->getParam('order_statuses', array());
        $export->from = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('from', '1980-01-01 00:00:00')));
        $export->to = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('to', '2092-01-01 00:00:00')));

        $fileName = 'taxify-export.csv';
 
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header("Content-Disposition: attachment; filename={$fileName}");
        header('Expires: 0');
        header('Pragma: public');
         
        $fh = @fopen('php://output', 'w');


        $count = 0;
        while ($count < 100000) {
            $rows = $export->fetch($count);
            if (count($rows) == 0) {
                break;
            }
            // Add header
            if ($count == 0) {
                fputcsv($fh, array_keys($rows[0]));
            }
            foreach ($rows as $row) {
                fputcsv($fh, $row);
            }
            $count = $count + 1;
        }
        fclose($fh);
        exit;
    }
}
