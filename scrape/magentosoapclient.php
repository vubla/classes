<?php


class MagentoSoapClient  {
     
    protected $error;
     
    protected $session_id;
     
     /**
      *
      * @var SoapClient 
      */
    protected $soap;

    protected $delay = 0;
     
    function __construct($host,$key, $name = 'vubla',$httpUser='',$httpPass='',$timeout=null, $delay=0)
    {
        $finalHost = $host;
        $options = array();
        $this->delay = (int)$delay;

        if(!empty($timeout))
        {
            $options = array(
                 'trace' => true,
                 'exceptions' => true,
                 'user_agent' => '', 
                 'connection_timeout' => $timeout
            );
            $oldTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $timeout);
        }
        if(trim($httpUser) != '')
        {
            $finalHost = '@'.$finalHost;
            $options['login'] = $httpUser;
            if(trim($httpPass) != '')
            {
                $finalHost = ':'.urlencode($httpPass).$finalHost;
                $options['password'] = $httpPass;
            }
            $finalHost = urlencode($httpUser).$finalHost;
        }
        $hosts = array(
                        'http://'. $finalHost .'/index.php/api/?wsdl', 
                        'http://'. $finalHost .'/index.php/api/soap/?wsdl' ,
                        API_URL.'/soap/wsdl_trimmer.php?url=http://'. $finalHost .'/api/?wsdl'
                        );
        $err = array();
        foreach($hosts as $wsdlhost)
        {
            try 
            {
                @$this->soap = new SoapClient($wsdlhost,$options);
               
                $this->session_id = $this->soap->login($name, $key);
                
                $err = array();
                break;
            } catch (SoapFault $e) {
                if($e->getMessage() == "SOAP extension is not loaded.")
                {
                    throw new SoapNotEnabledException("Soap is not enabled on remote site host: " . $host);
                }
                $err[] = $e;
            }
        }
        if(isset($oldTimeout))
        {
            ini_set('default_socket_timeout', $oldTimeout);
        }
        if(is_null($this->session_id)){
            //var_dump($err); exit;
            throw new MagentoLoginException('Failed for the following hosts'. print_r($hosts,true).'and key '. $key. ' <br><br> ALL ERRORS: <br> <br>  '. print_r($err, true));
        } 
        
        VOB::_n("Connection Established to ".$wsdlhost);
        
        
       
     }
     
     
  
     
     function call( $func, $arg = null, $attempts = 5) {
         if(is_null($this->session_id)){
             throw new MagentoLoginException("Call called, but we are not logged in?"); 
         }
        
         if(is_numeric($this->delay) && $this->delay > 0){
            usleep($this->delay);
         }
         $i = 0;
         while($i < $attempts)
         {
            try 
            {
                if(is_null($arg))
                {
                    return $this->soap->call($this->session_id, $func);
                }
                return $this->soap->call($this->session_id, $func, $arg);

            } 
            catch(SoapFault $e)
            {
                if($i == $attempts - 1)
                {
                    VOB::_n('============== BEGIN WARNING ======= ');
                    VOB::_n('After '.$attempts.' attempts we failed to recieve anything usefull');
                    VOB::_n(print_r($e->faultstring,true));
                    VOB::_n(print_r($arg, true));
                    VOB::_n($func);
                    VOB::_n('============== END WARNING ======= ');
                    $this->error = $e->getCode();
                }
            }
            $i++;
         }
         
     }
     
     function getLastError()
     {
         $err =  $this->error;
         $this->error = null;
         return $err;
     }
 }


