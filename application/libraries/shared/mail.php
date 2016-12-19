<?php

namespace Shared;
use Framework\Registry as Registry;
/**
 * Static class which sends Mail using different configurations
 */
class Mail {
    /**
     * Stores the conf
     */
    protected static $_conf = array();
    
    protected static function _mailgun() {
        if (isset(self::$_conf['mailgun'])) {
            return self::$_conf['mailgun'];
        }
        $mailConf = Utils::getConfig("mail")->mail->mailgun;

        $mg = new \Mailgun\Mailgun($mailConf->key);
        self::$_conf['mailgun'] = $mg;
        self::$_conf['domain'] = $mailConf->domain;
        self::$_conf['team'] = $mailConf->platform;

        return self::$_conf['mailgun'];
    }
    
    public static function _body($options) {
        $template = $options["template"];
        $view = new \Framework\View(array(
            "file" => APP_PATH . "/application/views/layouts/email/{$template}.html"
        ));
        foreach ($options as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }
    
    public static function send($options) {
        $mailgun = self::_mailgun();
        $domain = self::$_conf['domain'];
        // $org = $options["org"];
        // $body = self::_body($options);
        // $emails = isset($options["emails"]) ? $options["emails"] : array($options["user"]->email);



        $mailgun->sendMessage($domain, array(
            'from'    => "care@mobizdom.in",
            'to'      => $options["to"],
            'subject' => $options["subject"],
            'text'    => $options['body']


        ));
    }

    protected static function log($message = "") {
        $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
        $new = file_exists($logfile) ? false : true;
        if ($handle = fopen($logfile, 'a')) {
            $timestamp = strftime("%Y-%m-%d %H:%M:%S", time() + 1800);
            $content = "[{$timestamp}] {$message}\n";
            fwrite($handle, $content);
            fclose($handle);
            if ($new) {
                chmod($logfile, 0755);
            }
        } else {
            //echo "Could not open log file for writing";
        }
    }
}