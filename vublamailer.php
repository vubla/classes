<?php
require_once('/usr/share/php/libphp-phpmailer/class.phpmailer.php');

class VublaMailer{

    static $lastError;
        
    static function sendTest(){
        $mail = new PHPMailer();
        $mail->IsMail();

        $mail->AddAddress("rasmus@vubla.com");
        $mail->Subject = "Test 1";
        $mail->Body = "Test 1 of PHPMailer.";
        $mail->AddBcc(Settings::get('info_email_address'));
        if(!$mail->Send())
        {
            echo "Error sending: " . $mail->ErrorInfo;;
        }
        else
        {
            echo "Letter sent";
        }

    
    }
    
    static function generateEmailValSalt(){
        return 'fsfwfsdfsdfeweqwderfdsefdseefdefrdseefdefdcefdcfdvvvdfdfdfv';
    }
    
    static function sendActivationEmail($email){
        return true;
        $mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->isHtml(true);
		
		Vpdo::reset(DB_METADATA);
        $db = Vpdo::getVdo(DB_METADATA);
		$q = "SELECT * FROM `customers` WHERE `email` = $email";

		$state = $db->prepare($q);
		$state->execute();
			
		$rs = $state->fetchObject();
        $state->closeCursor();
		if(!isset($rs)) {
			self::$lastError = 'Send Activation Email error: E-mail '. $email .' blev ikke fundet i databasen';
            return false;
		}
		
        $key = md5($rs->id . $rs->email . self::generateEmailValSalt());

        $mail->AddAddress($rs->email);
        $mail->AddBCC(Settings::get('info_email_address'));
        $mail->Subject = iconv("UTF-8", "ISO-8859-1","Vubla - Velkommen til familien!");
        $link = '<a href="'.LOGIN_URL.'/user/activate/?email='.urlencode($rs->email).'&key='.urlencode($key).'">Klik her for at aktivere</a>';
        $mail->Body = '
Hej '.$rs->name.' <br /><br />

'.__('Tak fordi du har tilmeldt dig Vubla').' <br /><br />

'.__('Før du kan bruge Vubla skal din email aktiveres. Klik på nedenstående link for at aktivere.').' <br /><br />
 
 
'.$link.' <br /> <br />

'.__('Skulle linket ikke virke kan du kopiere nedenstående link ind i din browser.').'<br /> <br />
'.LOGIN_URL.'/user/activate/?email='.urlencode($rs->email).'&key='.urlencode($key).'<br /> <br />
'.__('Med venlig hilsen').' <br />
'.__('Hele Vubla Teamet').' <br /> 

';     
    
        
        $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
        if($mail->send()){
            return true;
        } else {
            self::$lastError = 'Send Activation Email error: ' . $mail->ErrorInfo;
            return false;
        }
    }
    
    static function sendContactRequest(){
        $mail = new PHPMailer();
			$mail->IsSMTP();
        $mail->isHtml(true);
        $mail->AddAddress(Settings::get('info_email_address'));
        $mail->Subject = "[Kontakt] ";
        $mail->Body = __("Der er en bruger der &oslash;nsker at blive kontaktet<br/><pre>");
        $mail->Body .= __("E-mail: ".$_POST['email']."\nTelefon: ".$_POST['phone']."</pre>");
        $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
        if($mail->send()){
            return true;
        } else {
            return false;
        }
	}
		
    static function sendContactForm(){
        $mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->isHtml(true);
        $mail->AddAddress(Settings::get('info_email_address'));
		$mail->AddReplyTo($_POST['email']);
        $mail->Subject = "[Kontakt] Besked fra " . $_POST['email'];
        $mail->Body = 'Besked fra ' . $_POST['fullname'] . ' (Vubla kontakt-form):<br />';
		$mail->Body .= '--------------------<br />';
        $mail->Body .= $_POST['message'] . '<br />';
		$mail->Body .= '--------------------<br /><br />';
		
		if(!empty($_POST['phone'])) {
			$mail->Body .= '<br /><br />Kontakt mig på telefon: ' . $_POST['phone'];
		}
		 $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
        if($mail->send()){
            return true;
        } else {
            return false;
        }
        
    }
    
    static function sendOnargiEmail($subject, $msg, $hash = null, $delay = 84600){
    	if(is_null($hash)){
    		$hash = md5($msg);
    	}
    	$db = vpdo::getVdo(DB_METADATA);
    	$stm = $db->prepare('SELECT * FROM onargi_log WHERE hash = ?');
        $stm->execute(array(md5($hash)));
        $onargi = $stm->fetchObject();
        $sendmail = false;
        if(is_object($onargi)){
        	 if($onargi->time < time()-$delay){
        	 	$sendmail = true;
        	 	$db->exec('update onargi_log set time = UNIX_TIMESTAMP(), count = 1 where hash = '.$db->quote(md5($hash)));
         	 	$msg = 'Do to Onargi Mail aggregation you did not recieve the ' . $onargi->count . ' similar email. <br />'.
         	 		'------------------------------------------------------------------------ <br />'.$msg;
        	 } else {
        	 	$sendmail = false;
        	 	$db->exec('update onargi_log set count = count + 1 where hash = '.$db->quote(md5($hash)));
        	 }
        } else {
        	$db->exec('insert into onargi_log (time, hash) values ('.time().','.$db->quote(md5($hash)).')');
        	$sendmail = true;
        	$msg = 'This is the first email in long time with this content. <br />'.
         	 		'------------------------------------------------------------------------ <br />'.$msg;
        }
       	
       	if($sendmail){
			$mail = new PHPMailer();
       		$mail->IsSMTP();
        	$mail->isHtml(true);
        	$mail->AddAddress(Settings::get('support_email_address'));
			$mail->From = 'noreply@vubla.com';
			$mail->FromName = 'Onargi';
			$mail->Sender = 'mailman@vubla.com';
        	$mail->Subject = $subject;
        	$mail->Body = $msg;
		
     	    if($mail->send()){
     	   		return true;
        	} else {
            	return false;
       		}
    	}
    	return true;
    
    }
		
	static function sendResetCodeEmail($email,$code,$name=null) {
		if(!$email || !$code)
		{
			return false;
		}
		
		if(isset($name)) {
			$name = ' ' . $name;
		}
		else{
			$name = '';
		}
		$mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->isHtml(true);

        $mail->AddAddress($email);
        $mail->AddBCC(Settings::get('info_email_address'));
        $mail->Subject = iconv("UTF-8", "ISO-8859-1",'Vubla - Nulstille password');
        $link = '<a href="'.LOGIN_URL.'/user/setpassword/?email='.urlencode($email).'&code='.urlencode($code).'">'.__('Klik her for at nulstille dit password.').'</a>';
        $mail->Body =
			__("Hej") ."$name,<br/><br/>".

			__("Hvis du ønsker at nulstille password for din vubla konto, venligst klik på linket nedenfor:") ."<br/><br/>".

			$link . "<br/><br/>".

			__("Du kan nulstille passwordet frem til {%t} timer efter modtagelsen af denne e-mail.", array('t'=>Settings::get('reset_password_minutes') / 60)) ."<br/><br/>".
			 
			__("Hvis du ikke ønsker at nulstille dit password så se venligst bort fra denne e-mail.") ."<br/><br/>".

			__("Med venlig hilsen") ."<br/>".
			__("Hele Vubla Teamet");
    
        
        $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
		
        if($mail->send()){
            return true;
        } else {
            self::$lastError = $mail->ErrorInfo;
            return false;
        }
	}
	
    static function sendCrawledEmail($wid){
        $db = Vpdo::getVdo(DB_METADATA);
        $q = "SELECT * FROM `customers` WHERE `wid` = ".(int)$wid;
    
		$state = $db->prepare($q);
		$state->execute();
			
		$rs = $state->fetchObject();

        $mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->isHtml(true);
        Language::init($wid);		

        $mail->AddAddress($rs->email);
        $mail->AddBCC(Settings::get('info_email_address'));
        $mail->Subject = iconv("UTF-8", "ISO-8859-1",__("Din webshop er nu blevet crawlet"));
        $mail->Body = 
__("Hej"). ' ' .$rs->name.' <br /><br />
'.__('Færdig - så er din webshop blevet crawlet.').'<br /><br />

'.__('Hvis du er i gang med at blive oprettet som kunde kan du nu fortsætte registreringsprocessen på').' <a href="https://login.vubla.com">login.vubla.com</a><br /><br />

'.__('Med venlig hilsen').'<br />
'.__('Hele Vubla Teamet').'<br />
';     
    
        self::logEmail($rs->email, $mail->Subject);
        $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
        if($mail->send()){
            return true;
        } else {
            self::$lastError = $mail->ErrorInfo;
            return false;
        }
    }
    
	static function sendDeletedConfirmation($email,$name=null) {
		if(!$email)
		{
			return false;
		}
		
		if(isset($name)) {
			$name = ' ' . $name;
		}
		else{
			$name = '';
		}
		$mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->isHtml(true);

        $mail->AddAddress($email);
        $mail->AddBCC(Settings::get('info_email_address'));
        $mail->Subject = iconv("UTF-8", "ISO-8859-1",__('Vubla - Afmeldt'));
        //$link = '<a href="'.LOGIN_URL.'/user/setpassword/?email='.urlencode($email).'&code='.urlencode($code).'">Klik her for at nulstille dit password.</a>';
        $mail->Body =
			__("Hej")."$name,<br/><br/>".

			__("Din Vubla bruger er nu afmeldt, du kan ikke længere benytte Vubla søgemaskinen.") ."<br/><br/>".
			
			__("Med venlig hilsen") ."<br/>".
			__("Hele Vubla Teamet");
    
        
        $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
		
        if($mail->send()){
            return true;
        } else {
            self::$lastError = $mail->ErrorInfo;
            return false;
        }
	}
	
	
	  static function sendPlainEmail($subject, $msg, $from, $to = 'info@vubla.com'){
    	
			$mail = new PHPMailer();
       		$mail->IsSMTP();
        	$mail->isHtml(true);
        //	$mail->AddAddress(Settings::get('info_email_address'));
            $mail->AddAddress($to);
		    $mail->From = 'support@vubla.com';
			
			$mail->Sender = 'mailman@vubla.com';
			$mail->FromName = $from;
        	$mail->Subject = $subject;
        	$mail->Body = $msg;
		      self::logEmail(Settings::get('info_email_address'), $mail->Subject);
     	    if($mail->send()){
     	   		return true;
        	} else {
            	return false;
       		}
    	
    
    }
      
      
      
      
        static function sendPaymentEmail($customer_email, $customer_name, $faktura_nr){
        
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->isHtml(true);
            $mail->AddAddress($customer_email);
            
            $mail->From = 'billing@vubla.com';
            $mail->FromName = 'Vubla';
            $mail->Sender = 'mailman@vubla.com';
       /*     if(defined('UNITTEST_MODE') && UNITTEST_MODE){
                $mail->Subject = __(Language::get()->getId().'Faktura' . "TESTEST" . $faktura_nr. rand(0,1000));
                
            } else { */
                $mail->AddBcc(Settings::get('info_email_address'));
                 $mail->Subject = __('Faktura');
         //  }
            $mail->Body = __('Hej {%name}<br><br>Hermed fremsendes faktura for denne måned', array('name'=>$customer_name)).'<br><br>';
            if(!defined('INVOICE_PATH')) define ('INVOICE_PATH','/var/vubla/invoices/' );
            $mail->AddAttachment(INVOICE_PATH.$faktura_nr.'.pdf', 'VublaFaktura.pdf');
            $mail->Body = iconv("UTF-8", "ISO-8859-1",$mail->Body);
            self::logEmail($customer_email, $mail->Subject);
            if($mail->send()){
                return true;
            } else {
                return false;
            }
        
    
    }
        
    static private function logEmail($to, $subject)
    {
        
        $time = date(DATE_ATOM); 
        @$fh = fopen('/var/vubla/crons/logs/email.log', 'a');
        $string = "To: "  . $to . " Subject: " .$subject . " Date " . $time . PHP_EOL;
        if($fh)
        {
            @fwrite($fh, $string);
        }
    }

}

?>
