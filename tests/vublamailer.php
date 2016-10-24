<?php


class VublaMailer {
    
    static function  sendOnargiEmail($subject, $msg, $hash = null, $delay = 84600){
        echo $subject .PHP_EOL.$msg;   
    }
    static function sendPlainEmail($msg, $subject){
         echo $subject .PHP_EOL.$msg;   
    }
    static function sendPaymentEmail(){}
}

