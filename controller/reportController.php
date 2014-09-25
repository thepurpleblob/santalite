<?php

namespace controller;

use core\coreController;

class reportController extends coreController {

    private function printCsvLine($items) {
        foreach($items as $index => $item) {
            if ($item==null) {
                $item = ' ';
            }
            $item = (string)$item;
            $item = str_replace("'", "\'", $item);
            $item = str_replace("\t", ' ', $item);
            if (!$item) {
            	$item = ' ';
            }
            $items[$index] = $item;
        }
        $line = implode("\t", $items);
        echo $line . "\n";
    }

    public function exportAction() {

        // get completed purchases
        $purchases = \ORM::for_table('purchase')->where('status', 'OK')->find_many();

        $date = date('Y');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=santa_$date.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        foreach ($purchases as $purchase) {
            $line = array(
                $purchase->type,
            	$purchase->bkgref,
                $purchase->day,
                $purchase->train,
                $purchase->surname,
                $purchase->title,
                $purchase->firstname,
                $purchase->address1,
                $purchase->address2,
                $purchase->address3,
                $purchase->address4,
                $purchase->postcode,
                $purchase->phone,
                $purchase->email,
                $purchase->adult,
                $purchase->child,
                $purchase->infant,
                $purchase->oap,
                $purchase->childagesboy,
                $purchase->childagesgirl,
                $purchase->comment,
                $purchase->payment,
                $purchase->bkgdate,
                $purchase->card,
                $purchase->action,
                $purchase->eticket,
                $purchase->einfo,
            );
            $this->printCsvLine($line);
        }
    }
    
    public function purchasesAction() {
        
        $this->require_login('organiser', $this->Url('report/purchases'));
        
        // get completed purchases
        $purchases = \ORM::for_table('purchase')->order_by_desc('bkgref')->find_many();
        
        $this->View('header');
        $this->View('report_purchases', array(
            'purchases' => $purchases,
        ));
        $this->View('footer');
        
    }
    
    private function row($label, $value) {
        if (!$value) {
            $value = '-';
        }
        $html = '<tr>';
        $html .= '<th>' . $label . '</th>';
        $html .= '<td>' . $value . '</td>';
        $html .= '</tr>';
        
        return $html;
    }
    
    public function purchaseAction($id) {
        
        $this->require_login('organiser', $this->url('report/purchase/'.$id));
        
        // find the purchase
        $purchase = \ORM::for_table('purchase')->find_one($id);
        if (!$purchase) {
            throw new \Exception('could not find purchase record for id='.$id);
        }
        
        // create table 'filling'
        $rows = array(
            'Booking reference' => $purchase->bkgref,
            'Status' => $purchase->status,
            'Booking date' => $purchase->bkgdate,
            'Booked day number' => $purchase->day,
            'Booked train number' => $purchase->train,
            'Name' => $purchase->title . ' ' . $purchase->firstname . ' ' . $purchase->surname,
            'Address 1' => $purchase->address1,
            'Address 2' => $purchase->address2,
            'Address 3' => $purchase->address3,
            'Address 4' => $purchase->address4,
            'Phone' => $purchase->phone,
            'Email' => $purchase->email,
            'Adults' => $purchase->adult,
            'Children' => $purchase->child,
            'Infants' => $purchase->infant,
            'Boys ages' => $purchase->childagesboy,
            'Girls ages' => $purchase->childagesgirl,
            'Payment' => '&pound; ' . number_format($purchase->payment / 100, 2),
            'Sage detail' => $purchase->statusdetail,
            'Sage auth number' => $purchase->txauthno,
            'Sage last 4 digits' => $purchase->last4digits,
        );
        $body = '';
        foreach ($rows as $label => $value) {
            $body .= $this->row($label, $value);
        }
        
        $this->View("header");
        $this->View('report_purchase', array(
            'statusok' => $purchase->status == 'OK',
            'body' => $body,
        ));
        $this->View('footer');
    }


}
