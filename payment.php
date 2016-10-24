<?php

class Payment {
    
    public $wid;
    private $db;
    public static $merchant = '33855044';
    public static $cardtypelock = 'mastercard,mastercard-debet-dk,mastercard-dk,visa,visa-dk,visa-electron,visa-electron-dk,dankort,edankort';
    public static $md5secret = '5J4196R6H5346d63yZb1wuVA4eE39Fr15xklg1Bj58hciYMqt422879LP3mTW7XI';
    
    function __construct($wid)
    {
        $this->db = vpdo::getVdo(DB_METADATA);
        $this->wid = $wid;
        
          Language::init($this->wid);
    }
  
  
    
    
    function process(){
        
       	$amount = (int) 100*self::getNextPrice($this->wid);
         
        $sql = 'SELECT *, customers.name as fullname FROM webshops inner join customers on webshops.id = customers.wid WHERE wid = ?';
      
        $stm = $this->db->prepare($sql);///'select * from webshops where id = ?');
        $stm->execute(array($this->wid));
        $webshop = $stm->fetchObject();
        $stm->closeCursor();
        $next_paydate = $webshop->paydate + 2592000;
      
        if($amount == 0)
        {
           $this->setNextPackIDAndPayDate($next_paydate);
           return true;
        }
        
        
        if(is_null($webshop) || is_null($webshop->transactionnum)){
        	$msg = 'There were no transaction num for this webshop. Therefore it must be disabled<br />';     	  	
	    	$msg .= '--------------------------------------------------------------------------------<br /><pre>';
	    	$msg .= print_r($webshop, true);
	    	$msg .= '</pre>';
	    	$msg .= '--------------------------------------------------------------------------------<br />';	
			echo $msg;
			vublamailer::sendPlainEmail('[Payment] No tid and paydate is passed' , $msg, 'Vubla Money Maker');
			return false;
        }


        if($webshop->transactionnum == -1 || $webshop->test_shop) {
            //It is a smart web or dandomain or similar
            return true;
        }
        
   
        $quickpay = new QuickPay(new stdClass());
        $quickpay->QUICKPAY_SECRET = self::$md5secret;
        $quickpay->protocol = 4; 
        $quickpay->merchant = self::$merchant;
        $quickpay->autocapture = 1;
        $quickpay->msgtype = 'recurring';
        $quickpay->currency = self::getCurrency($this->wid);
        
        echo 'Now withdrawing subscription for webshop '. $webshop->hostname . ' with id: ' .$this->wid .PHP_EOL;
        echo 'The Transactionsnumber for this webshop is: '. $webshop->transactionnum;
           
        $vatRaw = settings::get('payment_vat',$this->wid);
        $vat = doubleval($vatRaw);
        if($vat < 1 || $vat > 1.25)
        {
            vublamailer::sendPlainEmail('[Payment] Not Processed!' ,
                "Suspicious VAT detected: '$vat'<br/><br/>
                 Unconverted from DB: ".print_r($vatRaw,true)."<br/><br/>
                 Payment not completed - no money transfered, paydate noy changed.", 
                'Vubla Money Maker', 
                'support@vubla.com');
            return false;
        }
        $quickpay->amount = $amount*$vat;
        $quickpay->ordernumber  = $this->wid.'-'.time(); //$curr->ordernumber;//."-".$curr->id;
        $quickpay->description  = 'Host ' . $webshop->hostname;
        $quickpay->transaction  = $webshop->transactionnum;
        $quickpay->testmode     = $webshop->test_shop;        
        
        // Make actual withdrawal
        $resp = $quickpay->commit();
        
        echo "------ Quickpay Response ----" .PHP_EOL; 
        var_dump($resp);
        echo "------ End Quickpay Response ----" .PHP_EOL; 
        $resp['wid'] = $this->wid;
        $resp['pids'] = 'to come';
	
		$log = array();
		foreach($resp as $key=>$val){
			$log[$key] = $this->db->quote($val);
		}
        
        $log_keys = array_keys($log);
      	$sql = 'INSERT INTO payments_log ('.implode(', ',$log_keys) . ') values ('.implode(', ', $log).')';
        $this->db->exec($sql);
        
        
        
        
             
        
         
        if($resp['qpstat'] == '000'){
        	
        	
        	$q = 'UPDATE webshops SET is_trial = 0 WHERE id = '.(int)$this->wid;
			$this->db->exec($q);
			
			// That feature is off for now
			//$q = 'UPDATE payments SET paid = 1 WHERE wid = '.(int)$this->wid;
        	//$this->db->exec($q);
        	
        	$this->setNextPackIDAndPayDate($next_paydate);
        	
        	
        	
        	$msg = PHP_EOL.'Payment processed<br />' .PHP_EOL;     	  	
	    	$msg .= '--------------------------------------------------------------------------------<br /><pre>' .PHP_EOL;
	    	$msg .= print_r($webshop, true) . PHP_EOL;
	    	$msg .= print_r($resp, true);
	    	$msg .= '</pre>' .PHP_EOL;
	    	$msg .= '--------------------------------------------------------------------------------<br />' .PHP_EOL;	
			echo $msg;
			if($webshop->test_shop || (defined('UNITTEST_MODE') && UNITTEST_MODE)){
				vublamailer::sendPlainEmail('[Payment] Testing Payment!!' . rand(0,1000) , $msg, 'Vubla Money Maker');
			} else {
				vublamailer::sendPlainEmail('[Payment] Processed' , $msg, 'Vubla Money Maker');	
			}
            sleep(1);
            switch($webshop->next_pack_id){
                case 1:
                    $pack_name = __('Lille');
                    break;
                case 2:
                    $pack_name =  __('Mellem');
                    break;
                case 3:
                    $pack_name =  __('Stor');
                    
                    break;
                default:
                    $pack_name =  __('Speciel');
                 
            
            }
            $company = $webshop->company;
          
            $address = 'v/'.$webshop->fullname. "\n\n".
                       $webshop->address . "\n\n".
                       ($webshop->address2? $webshop->address2. "\n\n" : null) .
                       $webshop->postal . $webshop->city. "\n\n".
                       $webshop->phone ."\n\n".
                       $webshop->email;
                   

            $invoice_nr = $resp['ordernumber'];

            $date = date('d-m-Y',time());
            
            $purchases = array(
                    array(__('Vubla Interne Søgmaskine').' ('.$pack_name.')',$amount/100)
                    );
            
          
            $pdf = new Invoice();
            $pdf->createInvoice($invoice_nr,$date,$company,$address,$purchases, self::getCurrency($this->wid),$vat*100-100);
            $response = VublaMailer::sendPaymentEmail($webshop->email,$webshop->fullname, $invoice_nr);
			
        } else if(!$webshop->test_shop) {
            vublamailer::sendPlainEmail('[Payment] Failed!' ,"We said: <br/><pre>" . print_r ($quickpay, true) . "</pre><br />Quickpay said:<br />" ."<pre>" . print_r ($resp, true) .  " </pre><br /> ", 'Vubla Money Maker', 'support@vubla.com'); 
        }
        
      
         echo PHP_EOL . "------------- Finshed ------------- " . PHP_EOL . PHP_EOL;
         
         
        return true;
    }
   
    function setNextPackIDAndPayDate($next_paydate)
    {
        $q = 'Update webshops set pack_id = next_pack_id, paydate = '.$next_paydate.' where id = '. (int)$this->wid;
        $this->db->exec($q);
    }
   
 
   
    static function getFormInfo($wid){
        $db = vpdo::getVdo(DB_METADATA);
        $sql = 'SELECT *, customers.name as fullname FROM webshops inner join customers on webshops.id = customers.wid WHERE wid = ?';
        $stm = $db->prepare($sql);
        $stm->execute(array($wid));
        $webshop = $stm->fetchObject();
        $stm->closeCursor();
        
        $info = new stdClass();
        $info->protocol='4';
    	$info->msgtype='subscribe';
    	$info->merchant= self::$merchant;
    	$info->language='da';
    	$info->ordernumber = $wid.'-'.time();
    	$info->amount='0';
    	$info->currency = self::getCurrency($wid);
    	$info->continueurl= LOGIN_URL .'/payment/succes';
    	$info->cancelurl= LOGIN_URL .'/payment/failure';//kommersnart.com/error.php';
    	$info->callbackurl = LOGIN_URL .'/user/callback'; # see http://quickpay.dk/clients/callback-quickpay.php.txt
    	$info->cardtypelock= self::$cardtypelock;
    	$info->description =  $webshop->fullname . ' ' . $webshop->email . ' ' . $webshop->hostname;
    	$info->md5secret = self::$md5secret;
        $info->splitpayment = '0'; //should splitpayment be enabled on transaction, can be 0 => disabled, 1 => enabled
    	$info->testmode = VUBLA_DEBUG || $webshop->test_shop;
    	$info->CUSTOM_wid = $wid;
        $info->md5check = md5($info->protocol.$info->msgtype.$info->merchant.$info->language.$info->ordernumber.$info->amount.$info->currency.$info->continueurl.$info->cancelurl.$info->callbackurl.$info->cardtypelock.$info->description.$info->testmode.$info->splitpayment.$info->md5secret);

        return $info;
        
    }

    static function makeSubscription($data){
		$msg = 'Subscription attempt recieved.  <br />';
    	$db = vpdo::getVdo(DB_METADATA);
    
	    $payment = new stdClass();
	    $payment->msgtype = $data['msgtype'];
	    $payment->ordernumber = $data['ordernumber'];
	    $payment->amount = $data['amount'];
	    $payment->currency = $data['currency'];
	    $payment->time = $data['time'];
	    $payment->state = $data['state'];
	    $payment->qpstat = $data['qpstat'];
	    $payment->qpstatmsg = $data['qpstatmsg'];
	    $payment->chstat = $data['chstat'];
	    $payment->chstatmsg = $data['chstatmsg'];
	    $payment->merchant = $data['merchant'];
	    $payment->merchantemail = $data['merchantemail'];
	    $payment->transaction = $data['transaction'];
	    $payment->cardtype = $data['cardtype'];
	    $payment->cardnumber = $data['cardnumber'];
	    $payment->cardexpire = $data['cardexpire'];
	    $payment->splitpayment = $data['splitpayment'];
	    $payment->fraudprobability = $data['fraudprobability'];
	    $payment->fraudremarks = $data['fraudremarks'];
	    $payment->fraudreport = $data['fraudreport'];
	    $payment->fee = $data['fee'];
	    $payment->secret = self::$md5secret;
	    
	    if(md5(implode(get_object_vars($payment))) == $data['md5check']){
	    	$payment->validmd5 = true;
	    	$msg .= 'Md5 ok. <br />';
	    	$result = 'received';
	    }
	    else {
	    	$payment->validmd5 = false;
	    	$msg .= 'Md5 FAILED!!!. <br />';
	    	$result = 'failed';
	    }
	   	$payment->wid = $data['CUSTOM_wid'];
	   	$payment->md5check = $data['md5check'];
	    
	    
	    
	   	if($payment->qpstat == '000' && $payment->validmd5){
	   		//$msg .= 'UPDATE webshops SET transactionnum = '.$db->quote($payment->transaction).' WHERE id = ' . (int)$payment->wid;
	   		$db->exec('UPDATE webshops SET transactionnum = '.$db->quote($payment->transaction).' WHERE id = ' . (int)$payment->wid);
	   		$msg .= 'The customer now  has valid trannum and we are ready to roll<br />'; 
	   	
	   	} else {
	   		$msg .= 'I did not do anything <br />';
	   		
	   	}
	    
	    
	    
	   	
	    $stm = $db->prepare('select * from customers where wid = ?');
	    $stm->execute(array($payment->wid));
	    $payment->user = $stm->fetchObject();
        $stm->closeCursor();
	    
	    unset($payment->user->session);
	    unset($payment->user->cookie);
	    unset($payment->secret);
	   
	    $stm = $db->prepare('select * from webshops where id = ?');
	    $stm->execute(array($payment->wid));
	    $payment->webshop = $stm->fetchObject();
        $stm->closeCursor();
	   
	   
	//    $db->exec('');
	   
	
	   	
	    $msg .= '--------------------------------------------------------------------------------<br /><pre>';
	    $msg .= print_r($payment, true);
	    $msg .= '</pre>';
	    $msg .= '--------------------------------------------------------------------------------<br />';
	    
	    
		vublamailer::sendPlainEmail('[Subscr] Subscription '. $result , $msg, 'Vubla Money Maker');
            
    
    }

	static function getTnum($wid){
		$q = 'Select transactionnum from webshops where id = ?';
		$db = vpdo::getVdo(DB_METADATA);
		return $tnum = $db->fetchOne($q, array($wid));
	}

	static function isTrial($wid){
		$q = 'select is_trial as payed from webshops where id = ?';
		$db = vpdo::getVdo(DB_METADATA);
		return $db->fetchOne($q, array($wid));
	}

	static function setNextPackID($pack_id, $wid){
		$db = vpdo::getVdo(DB_METADATA);	
		//if($db->fetchOne('select count(*) from webshops where id = ? and pack_id = next_pack_id', array($wid)) > 0){
			$q = 'update webshops set next_pack_id = '.$db->quote($pack_id).' where id = '.$db->quote($wid);
			$db->exec($q);
			return true;
		//} else {
		//	return false;
			
		//}
		//$q = 'update payments set price = (select price from subscription_packages where id = '.$db->quote($pack_id).') where paid = 0 and  wid = '.(int)$wid;	
		//$db->exec($q);
	}
	static function getNextPackId($wid){
		$db = vpdo::getVdo(DB_METADATA);	
		$q = 'select next_pack_id from webshops where id = '.$db->quote($wid);
		return $db->fetchOne($q);

	}

   static function getPackId($wid){
        $db = vpdo::getVdo(DB_METADATA);    
        $q = 'select pack_id from webshops where id = '.$db->quote($wid);
        return $db->fetchOne($q);
     
    }
    
    static function disablePayments($wid) {
        $db = vpdo::getVdo(DB_METADATA);
        $db->beginTransaction();
        $res = $db->exec('UPDATE webshops SET transactionnum = -1 WHERE id = ' . $db->quote($wid));
        if($res > 1) {
            $db->rollback();
            return false;
        }
        $db->commit();
        $msg = 'Payments for webshop with id: ' . $wid . ' has been disabled';
        vublamailer::sendPlainEmail('[Disable Payment] '. 'success' , $msg, 'Vubla Money Maker');
        return true;
    }


    static function cron(){
        $db = vpdo::getVdo(DB_METADATA);
        $sql = 'SELECT id FROM webshops where paydate < UNIX_TIMESTAMP() ';
        $stm = $db->prepare($sql);
        $stm->execute();
        while($row = $stm->fetchObject()){
            Language::init($row->id);
            $payment = new Payment($row->id);
            $payment->process();
            $payment = null;
        }
        $stm->closeCursor();
        
    
    }
    
    static function getNextPrice($wid){
           Language::init($wid);
           $lang_id = Language::get()->getId(); 
        
           return  vpdo::getVdo(DB_METADATA)->fetchOne('Select value from subscription_packages s inner join prices p using (price_id) inner join webshops w on s.id = w.next_pack_id   where w.id = ? and language_id = ?',array($wid, $lang_id));
    }
    
    static function getCurrency($wid)
    {
          Language::init($wid);
          $lang_id = Language::get()->getId(); 
        
           return  vpdo::getVdo(DB_METADATA)->fetchOne('Select currency from subscription_packages s inner join prices p using (price_id) inner join webshops w on s.id = w.next_pack_id   where w.id = ? and language_id = ?',array($wid, $lang_id));
        
    }
    
    static function getPrice($wid){
            Language::init($wid);
            $lang_id = Language::get()->getId();
            return  vpdo::getVdo(DB_METADATA)->fetchOne('Select value from subscription_packages s inner join prices using (price_id)  inner join webshops w on s.id = w.pack_id   where w.id = ? and language_id = ?',array($wid, $lang_id));
    }
}

