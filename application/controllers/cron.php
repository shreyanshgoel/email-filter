<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */
use Shared\Utils;
use Framework\{Registry, ArrayMethods, RequestMethods};
use Shared\Services\{Db, User as Usr, Performance as Perf};
use Shared\Mail as Mail;
use Curl\Curl as Curl;

class Cron extends Shared\Controller {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->noview();
        if (php_sapi_name() != 'cli') {
            $this->_404();
        }
    }

    public function index($type = "daily") {

        switch ($type) {
            case 'minutely':
                $this->_minutely();
                break;

            case 'hourly':
                $this->_hourly();
                break;

            case 'daily':
                $this->_daily();
                break;

            case 'weekly':
                $this->_weekly();
                break;

            case 'monthly':
                $this->_monthly();
                break;
        }
    }

    // execute every 5 minutes
    protected function _minutely() {

    }

    protected function _hourly() {
        
    }

    protected function _weekly() {
        // implement
    }

    /**
     * Run on 1st of every month
     */
    protected function _monthly() {
    
    }

    protected function _daily() {
        $this->log("CRON Started");
        $this->filter();
        $this->log("CRON ended");
    }

    protected function filter(){

        $tasks = models\Task::all([
            'live = ?' => 1
            ]);

        foreach ($tasks as $t) {

            $t->live = 0;
            $t->save();

            $date = $t->created;

            $date2 = date('Y-m-d', strtotime($date . '- ' . $t->days . ' days'));

            $str = strtotime($date2);

            $file = APP_PATH . '/public/uploads/' . $t->csv . '.csv';

            $content = '';

            if(($handle = fopen($file, "r")) !== FALSE){

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        
                     $curl = new Curl();
                        
                     $curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
                        
                     $response = $curl->get('http://api.madapi.xyz/?q=' . $data[0]);
                        
                     $curl->close();

                     $details = json_decode($response);


                     if(strtotime($details->{"active"}) > $str){

                         $content.= $data[0] . '
';
                     }

                 }

                 fclose($handle);

             }

             if(!empty($content)){

                $to = $t->email;
                $subject = "Filtered Emails";

                $m = Mail::send([
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $content
                    ]);

             }

            unlink($file);

            $t->status = 1;
            $t->save();

        }


    }


}