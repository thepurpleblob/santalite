<?php

namespace controller;

use core\coreController;

class reportController extends coreController {
    
    private function printCsvLine($items) {
        foreach($items as $index => $item) {
            if ($item==null) {
                $item = '';
            }
            $item = (string)$item;
            $item = str_replace("'", "\'", $item);
            $item = str_replace("\t", ' ', $item);
            $items[$index] = $item;
        }
        $line = implode("\t", $items);
        echo $line . "\n";
    }

    public function exportAction() {

        // get completed purchases
        $purchases = \ORM::for_table('purchase')->where('status', 'OK')->find_many();
        
        $date = date('Y_M_d');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=santa_$date.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        foreach ($purchases as $purchase) {
            $line = array(
                $purchase->type,
                $purchase->day,
                $purchase->train,
                $purchase->bkgref,
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


}