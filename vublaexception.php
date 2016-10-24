<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
/**
 * Default Exception
 * @author rasmus
 *
 */
class VublaException extends Exception {

    protected $onargiDelay = 84600;
    protected $sendEmail = true;
    function getOnargiDelay(){
        return $this->getOnargiDelay;
    }
    
    function getSendEmail(){
        return $this->sendEmail;
    }


}
?>