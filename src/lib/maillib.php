<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Booking model
 */

namespace thepurpleblob\santa\lib;

use Exception;

/**
 * Class maillib
 * @package thepurpleblob\railtour\library
 */
class maillib {

    protected $mailer;

    protected $purchase;

    protected $extrarecipients;

    protected $bm;

    protected $controller;

    /**
     * Initialise email service
     */
    public function initialise($controller, $purchase, $bm) {
        global $CFG;

        // Set the purchase and booking library/model
        $this->purchase = $purchase;
        $this->bm = $bm;
        $this->controller = $controller;

        // Create transport
        $transport = new \Swift_SmtpTransport($CFG->smtpd_host);

        // Create mailer
        $this->mailer = new \Swift_Mailer($transport);

        // In case there are non
        $this->extrarecipients = array();
    }

    /**
     * Get list of people to send email to
     * @return array
     */
    private function getRecipients() {
        $recipients = $this->extrarecipients;
        if ($this->purchase->email) {
            $recipients[] = $this->purchase->email;
        }

        return $recipients; 
    }

    /**
     * Add extra recipients to send extra copies of mail
     * @param array $recipients
     */
    public function setExtrarecipients($recipients) {
        $this->extrarecipients = $recipients;
    }

    /**
     * Get the date in a readable format given day number
     * @param int $number
     */
    protected function getReadableDate($number) {
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $count = 1;
        foreach ($dates as $date) {
            if ($count == $number) {
                return date('jS F Y', $date->date);
            }
            $count++;
        }

        return '-';
    }

    /**
     * Get the time in a readable format given number
     * @param into $number
     *
     */
    protected function getReadableTime($number) {
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $count == 1;
        foreach ($times as $time) {
            if ($count == $number) {
                return $time->time;
            }
            $count++;
        }

        return '-';
    }

    /**
     * Send notification of completion
     */
    public function confirm() {

        // get fares
        $total = number_format($this->purchase->payment / 100, 2);

        // date/time
        $date = $this->getReadableDate($this->purchase->day);
        $time = $this->getReadableTime($this->purchase->train);
        
        // Get messages
        $body = $this->controller->renderView('email_confirm', array(
            'purchase' => $this->purchase,
            'date' => $date,
            'time' => $time,
            'total' => $total,
        ));
        $bodytxt = $this->controller->renderView('email_confirm_txt', array(
            'purchase' => $this->purchase,
        ));

        foreach ($this->getRecipients() as $recipient) {
            $message = (new \Swift_Message())
            ->setSubject('B&KR Santa Trains - Confirmation (' . $this->purchase->bkgref . ')')
            ->setFrom('noreply@srps.org.uk')
            ->setTo($recipient)
            ->setBody($bodytxt)
            ->addPart($body, 'text/html');

            $this->mailer->send($message);
            $this->controller->log('Sending confirm email to ' . $this->purchase->bkggref );
        }
    }

    /**
     * Send notification for decline/fail
     */
    public function decline() {

        // Get messages
        $body = $this->controller->renderView('email_fail', array(
            'purchase' => $this->purchase,
        ));
        $bodytxt = $this->controller->renderView('email_fail_txt', array(
            'purchase' => $this->purchase,
        ));

        foreach ($this->getRecipients() as $recipient) {
            $message = (new \Swift_Message())
            ->setSubject('B&KR Santa Trains - Payment Declined (' . $this->purchase->bookingref . ')')
            ->setFrom('noreply@srps.org.uk')
            ->setTo($recipient)
            ->setBody($bodytxt)
            ->addPart($body, 'text/html');

            $this->mailer->send($message);
            $this->controller->log('Sending decline email to ' . $recipient . ' ' . $this->purchase->bkgref );
        }
        
    }
}
