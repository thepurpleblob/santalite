<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Limits controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;

class reportController extends coreController {

    private function printCsvLine($items) {
        foreach($items as $index => $rtype) {
            if (is_array($rtype)) {
                list($item, $length) = $rtype;
            } else {
                $item = $rtype;
                $length = null;
            }
            if ($item==null) {
                $item = ' ';
            }
            $item = (string)$item;
            $item = str_replace("'", "\'", $item);
            $item = str_replace("\t", ' ', $item);
            if (!$item) {
            	$item = ' ';
            }
            if ($length) {
                $item = substr($item, 0, $length);
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
                array($purchase->surname, 20),
                array($purchase->title, 12),
                array($purchase->firstname, 20),
                array($purchase->address1, 25),
                array($purchase->address2, 25),
                array($purchase->address3, 25),
                array($purchase->address4, 25),
                array($purchase->postcode, 8),
                array($purchase->phone, 15),
                array($purchase->email, 50),
                $purchase->adult,
                $purchase->child,
                $purchase->infant,
                $purchase->oap,
                $purchase->childagesboy,
                $purchase->childagesgirl,
                array($purchase->comment, 39),
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

        // get form filter inputs
        $status = 'all';
        $statusoptions = array(
            'all' => 'All',
            'ok'=> 'OK',
            'reconcile' => 'Reconcile',
            'fail' => 'Failures'
        );
        if ($request = $this->getRequest()) {
            $status = $request['status'];
        }

        // get completed purchases
        $purchases = \ORM::for_table('purchase')->order_by_desc('id')->find_many();

        // filter by reduction
        $filtered = array();
        foreach ($purchases as $purchase) {
            if (empty($purchase->status) || ($purchase->status == '-')) {
                $class = 'santa-reconcile';
                $value = $this->Url('report/reconcile/'.$purchase->id());
                $displaystatus = '<button type="button" class="btn btn-success btn-sm reconcile" value="'.$value.'">Reconcile</button>';
            } else if ($purchase->status == 'OK') {
                $class = '';
                $displaystatus = 'OK';
            } else {
                $class = 'santa-fail';
                $displaystatus = $purchase->status;
            }
            $purchase->class = $class;
            $purchase->displaystatus = $displaystatus;
            if ($status=='reconcile' && !empty($purchase->status)) {
                continue;
            }
            if ($status=='ok' && $purchase->status !== 'OK') {
                continue;
            }
            if ($status=='fail' && (empty($purchase->status) || ($purchase->status=='OK'))) {
                continue;
            }
            $filtered[] = $purchase;
        }

        // Select form
        $form = new \stdClass;
        $form->select = $this->form->select('status', 'Status', $status, $statusoptions);

        $this->View('report_purchases', array(
            'ispurchases' => !empty($filtered),
            'purchases' => $filtered,
            'status' => $status,
            'form' => $form,
        ));

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
            'Postcode' => $purchase->postcode,
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
            'purchase' => $purchase,
            'statusok' => $purchase->status == 'OK',
            'body' => $body,
        ));
        $this->View('footer');
    }

    public function reconcileAction($id, $status) {
        $this->require_login('organiser', $this->url('report/purchase/'.$id));

        $purchase = \ORM::for_table('purchase')->find_one($id);
        if (!$purchase) {
            error_log('could not find purchase record for id='.$id);
        }
        if ($purchase->status == 'OK') {
            error_log('Purchase already status OK for id='.$id);
        }
        if (($status=='OK') || ($status=='UNPAID')) {
            $purchase->status = $status;
        }
        $purchase->save();
        die;
    }


}
