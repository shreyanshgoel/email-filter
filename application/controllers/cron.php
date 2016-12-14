<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */
use Shared\Utils;
use Framework\{Registry, ArrayMethods, RequestMethods};
use Shared\Services\{Db, User as Usr, Performance as Perf};

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
        $this->log('Starting Memory at: ' . memory_get_usage());

        $this->_performance();
        $this->log('Peak Memory at: ' . memory_get_peak_usage());
        $this->_invoice();
        $this->_rssFeed();

    }


}