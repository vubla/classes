<?php
class User {
    
    private static $salt = 'Super Secret Salti7lkumtnryehj678li,5muy57k657i,tyjrmhentyj374k6i,jmrythenry357k64ury,j hmnrwyjku,yrkhm yjkry,jmhrjykue,tjmhjykjkeutjmhjruektu';
   
    var $db = null;
    var $failed = false; // failed login attempt
	var $error = null; //error msg
	var $date; // current date GMT
	var $id = 0; // the current user's id
	
    function __construct() {
		$this->db = VPDO::getVdo(DB_METADATA);
        if(isset($_GET['master']) && isset($_GET['id'])){
            /**
             * If the master parameter is set we should check that it is correctly set.
             */     
            $_SESSION['email'] = $this->db->fetchOne('select hostname from webshops where id = ?', array( $_GET['id']));
            $_SESSION['master'] = $_GET['master'];
            $_SESSION['logged'] = true;
            $_SESSION['uid'] = $_GET['id'];
            $this->_checkMaster();
            $this->id =  $_SESSION['uid'];
          
            return;
          
        }  
        if( isset($_SESSION['master'])){
             $this->_checkMaster();
            $this->id =  $_SESSION['uid'];
            return;
        }
        
		if (array_key_exists('logged', $_SESSION) && $_SESSION['logged']) {
			$this->_checkSession();
		}
	}
	
    private function _checkMaster(){
            $salt = "superman";
         $db = VPDO::getVdo(DB_METADATA);
         $row =$db->getRow('select * from secure_logins where ip = '.$db->quote($_SERVER['REMOTE_ADDR']).' and uid = '.$db->quote($_SESSION['uid']) );
        $email = $db->fetchOne('select email from customers  where id = ?', array($_SESSION['uid']));
        $md5 = md5($salt. $row->time. $row->name. $_SERVER['REMOTE_ADDR']. $email . $salt);
           $expire_time = (int) Settings::get('admin_login_session_time');
        if($row->time < time() - $expire_time){
              $this->error = 'Master expired.';
             $this->_logout(); 
        }
        if($md5 != $_SESSION['master']){
      
              $this->error = 'No session.';
             $this->_logout(); 
        }
    }
    
    
	function validate_user_data($data) {	
		$dbadmin = new WebshopDbManager();
		
		##### URL #####
		if(isset($data['url'])) {
			if(empty($data['url']) || strlen($data['url']) < 4) {
				return "url";
			}
			
			$url = preg_replace(array('/http:\/\//','/\/$/'),'',$data['url']);	

			$url = $this->db->quote($url);
			
			if($dbadmin->check_webshop_exists($url)) {
				return "url";
			}
		}  
		elseif(isset($data['email'])) {
        ##### EMAIL #####
			if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
				$email = $this->db->quote($data['email']);
				
				if($dbadmin->check_user_exists($email)) {
					return "email";
				}
			}
			else {
				return "email";
			}
        }
		elseif(isset($data['fullname'])) {
		##### Fullname ####
		    $data['fullname'] = strip_tags($data['fullname']);
			if(strlen($data['fullname']) < 3){
				return "fullname";
			}
		}
		elseif(isset($data['phone'])) {
		   $data['phone'] = strip_tags($data['phone']);
        ##### Phone ####
			if(strlen($data['phone']) < 3){
				return "phone";
			}
		}
		elseif(isset($data['address'])) {
		     $data['address'] = strip_tags($data['address']);
         ##### Adress ####
			if(strlen($data['address']) < 3){
				return "address";
			}
		}
		elseif(isset($data['postal'])) {
		##### Postal ####
		     $data['postal'] = strip_tags($data['postal']);
			if(strlen($data['postal']) < 3){
				return "postal";
			}
		}
		elseif(isset($data['city'])) {
        ##### City ####
            $data['city'] = strip_tags($data['city']);
			if(strlen($data['city']) < 1){
				return "city";
			}
		}
		elseif(isset($data['password'])) {
        ##### Password ####			
			if(strlen($data['password']) < 7) {
				return "password";
			}
			elseif(isset($data['password2'])) {
				if($data['password'] != $data['password2']) {
					return "password2";
				}
			}
		}
	}

/**
 * For the new login
 */
function create_new($data){
        $errors = array();
        $dbadmin = new WebshopDbManager();
       
        ##### EMAIL #####
        if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $email = $this->db->quote($data['email']);
            
            if($dbadmin->check_user_exists($email)) {
                $errors['email'] = __('Kunden er allerede registreret');
            }
        } else {
            $errors['email'] = __('Emailen er ugyldig.');
        }
       
        ##### Password ####
        if(strlen($data['password']) < 7) {
            $errors['password'] = 'Dit valgte password er for kort.';
        } else {      
            $password = $this->db->quote(md5($data['password'].self::$salt));
        }
        
  
        ##### Add to database ####      
        try {
            if(empty($errors)) {
                $dbadmin->generate_new($email,$password);
                
//              $dbadmin->generate($url,$subscription,$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master);
          
                 
                return '';
            }
            else {
                return $errors;
            }
        } catch(VublaException $e){
            ob_clean();
            echo __('Vi beklager der skete en fejl. Vi arbejder på sagen');
            VublaMailer::sendOnargiEmail('Failed user creation', 'We failed to create a user with data: <br /><br /><pre>'.print_r($data,true).'<br />Error:<br />'.print_r($e,true).'</pre>');
            if(VUBLA_DEBUG){
                var_dump($e);
            }
               
            exit;
        }
           
    }
    
	/**
     * Only used by api now :)
     */
	function create($data,$sendActivationEmail = false){
        $errors = array();
		$dbadmin = new WebshopDbManager();
		
		##### PACKAGE #####
		if(!Package::isValidPackage($data['subscription'])) {
			$errors['subscription'] = __('Du har valgt en ugyldig pakke ({%package})', array('package'=> $data['subscription']));
		}
		else {
			$subscription = $this->db->quote($data['subscription']);
		}
		
		##### URL #####
		$url = preg_replace(array('/http:\/\//','/\/$/'),'',$data['url']);	

		$url = $this->db->quote($url);
		
		if($dbadmin->check_webshop_exists($url)) {
			$errors['url'] = __('Webshoppen er allerede registreret');
		}
        
        ##### EMAIL #####
        if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $email = $this->db->quote(strip_tags($data['email']));
			
			if($dbadmin->check_user_exists($email)) {
				$errors['email'] = 'Kunden er allerede registreret';
			}
        } else {
            $errors['email'] = 'Emailen er ugyldig.';
        }
        
        ##### Fullname ####
        if(strlen($data['fullname']) > 3){
            $name = $this->db->quote(strip_tags($data['fullname']));
        } else {
            $errors['fullname'] = 'Ugyldigt navn. Husk at det skal være dit fulde navn.';
        }
		
		##### Company ####
		$company = $this->db->quote(strip_tags($data['company']));
        
        ##### Phone ####
        if(strlen($data['phone']) > 3){
            $phone = $this->db->quote(strip_tags($data['phone']));
        } else {
            $errors['phone'] = 'Ugyldigt telefon nummer';
        }
        
         ##### Adress ####
        if(strlen($data['address']) > 3){
            $address = $this->db->quote(strip_tags($data['address']));
        } else {
            $errors['address'] = 'Ugyldig adresse.';
        }
        
        ##### Adress2 ####
        $address2 = $this->db->quote(strip_tags($data['address2']));

        
        ##### Postal ####
        if(strlen($data['postal']) > 3){
            $postal = $this->db->quote(strip_tags($data['postal']));
        } else {
			$errors['postal'] = 'Ugyldigt postnummer.';
        }
        
        ##### City ####
        if(strlen($data['city']) > 1){
            $city = $this->db->quote(strip_tags($data['city']));
        } else {
           $errors['city'] = 'Ugyldigt postnummer.';
        }
        
        ##### Password ####
        if(strlen($data['password']) < 7) {
			$errors['password'] = 'Dit valgte password er for kort.';
		}
		elseif($data['password'] != $data['password2']) {
			$errors['password'] = 'Kodeordene skal stemme overens.';
		}
		else {		
            $password = $this->db->quote(md5($data['password'].self::$salt));
		}
        
        ####### Master #######
        $master = $this->db->quote($data['master']);


		##### Add to database ####		
		try {
			if(empty($errors)) {
				$dbadmin->generate($url,$subscription,$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master);

                if($sendActivationEmail) {
    				if(!VublaMailer::sendActivationEmail($email)) {
    					return array("Could not send E-Mail but the registration of " . $url . " did succeed - Please contact us");
    				}
                }

				return '';
			}
			else {
				return $errors;
			}
		} catch(VublaException $e){
			ob_clean();
			echo __('Vi beklager der skete en fejl. Vi arbejder på sagen');
            VublaMailer::sendOnargiEmail('Failed user creation', 'We failed to create a user with data: <br /><br /><pre>'.print_r($data,true).'<br />Error:<br />'.print_r($e,true).'</pre>');
			if(VUBLA_DEBUG){
				var_dump($e);
			}
			   
			exit;
		}
    }
    
    static function defaults() {
	   $_SESSION['logged'] = false;
	   $_SESSION['uid'] = 0;
	   $_SESSION['email'] = '';
	   $_SESSION['cookie'] = 0;
	   $_SESSION['remember'] = false;
    }
    
    function checkLogin($data,$activationLocation = LOGIN_URL){
        
        $email = $this->db->quote($data['email']);
		$password = $this->db->quote(md5($data['password'].self::$salt));
       // $password = $this->db->quote($data['password']);
     
       
        $remember = false;
        if(isset($data['remember'])) $this->db->quote($data['remember']);
		$sql = "SELECT * FROM customers WHERE " .
			"email = $email AND " .
			"pwd = $password";
       
		$result = $this->db->getRow($sql);
        if ( is_object($result) ) {/*
            if(!$result->email_activated){
                $this->failed = true;
                $this->error = "Din email er ikke aktiveret endnu. <br/> <a href=\"" .$activationLocation .'">Tryk her for n&aelig;rmere information</a>';
			    $this->_logout();
			    return false;
		    }*/
			$this->_setSession($result, $remember);
			return true;
		} else {
			$this->failed = true;
			$this->error = __('Bruger og/eller kodeord er forkerte.');
			$this->_logout();
			return false;
		}
		
		
    }
    
    /**
     * This function is nessecary because the original does logout, session and much more. 
     */
    static function checkLoginAPI($data){
        $db = vpdo::getVdo(DB_METADATA);
        $email = $db->quote(strip_tags($data['email']));
        $password = $db->quote(md5($data['password'].self::$salt));
   
        $sql = "SELECT * FROM customers WHERE " .
            "email = $email AND " .
            "pwd = $password";
      
        $result = $db->getRow($sql);
      
        if ( is_object($result) ) {
            return $result;
        } else {
            return false;
        }
        
        
    }
	
    function _setSession(&$values, $remember, $init = true) {
        $this->id = $values->id;
        $_SESSION['uid'] = $this->id;
        $_SESSION['email'] = htmlspecialchars($values->email);
        $_SESSION['cookie'] = $values->cookie;
        $_SESSION['logged'] = true;
        $_SESSION['time'] = time();
        
        if ($remember) {
         
            $this->updateCookie($values->cookie, true);
        } else {
            
        }

        if ($init) {
            $session = $this->db->quote(session_id());
            $ip = $this->db->quote($_SERVER['REMOTE_ADDR']);

            $sql = "UPDATE customers SET session = $session, ip = $ip WHERE " .
                    "id = $this->id";
            $this->db->exec($sql);
        }
    }
    
    function updateCookie($cookie, $save) {
        $_SESSION['cookie'] = $cookie;
        if ($save) {
            $cookie = serialize(array($_SESSION['email'], $cookie) );
            $expire_time = Settings::get('cookie_expire_time');
            setcookie('vublawebLogin', $cookie, time() + $expire_time, '/');
        }
    }
    
    
    function _checkSession() {
    	$email =   $this->db->quote($_SESSION['email']);
    	$cookie =  $this->db->quote($_SESSION['cookie']);
    	$session = $this->db->quote(session_id());
    	$ip =      $this->db->quote($_SERVER['REMOTE_ADDR']);
    
    	$sql = "SELECT * FROM customers WHERE " .
    		"(email = $email) AND (cookie = $cookie) AND " .
    		"(session = $session) AND (ip = $ip)";
    
    	$result = $this->db->getRow($sql);
       $expire_time = (int) Settings::get('login_session_time');
       if($_SESSION['time'] < time() - $expire_time){
           $this->error = __('Du har været inaktiv for længe.');
           $this->_logout();
       }
       
    	if (is_object($result) ) {
    		$this->_setSession($result, false, false);
    	} else {
    		$this->_logout();
    	}
    }
    
    function _logout($redirectUrl = LOGIN_URL){
       
        session_destroy();
        setcookie('vublawebLogin', 'no!', time() - 31104000, '/');
        $get_param = '';
		if(!is_null($this->error)) {
			$get_param = '&error=' . base64_encode($this->error);
		}	
        echo header('Location: '.$redirectUrl.'?iso='.language::get()->getIso().$get_param);
        exit;
    }
    



    
    
    
    function wid(){
        return self::getWid($this->id);
    }
    
    static function getWid($uid){
    
        return Vpdo::getVdo(DB_METADATA)->fetchOne('Select wid from customers where id = ?', array($uid));
    }
      
    static function getHost($uid){
        
        return Vpdo::getVdo(DB_METADATA)->fetchOne('Select hostname from webshops where id = ?', array(self::getWid($uid)));
    }
    
	public function changePassword($cid,$data) {
		if(!isset($data['oldPassword'])) {
			return false;
		}
		if(!isset($data['password'])) {
			return false;
		}
		if(!isset($data['password2'])) {
			return false;
		}
		if(strlen($data['password']) < 7) {
			$errors[] = __("Dit nye kodeord er ikke langt nok.");
			return $errors;
		}
		if($data['password'] != $data['password2']) {
			$errors[] = __("De nye indtastede kodeord stemte ikke overens.");
			return $errors;
		}
		$errors = array();
		$id = $this->db->quote($cid);

		$oldPassword = $this->db->quote(md5($data['oldPassword'].self::$salt));

		$sql = "SELECT count(*) FROM customers WHERE " .
			"id = $id AND " .
			"pwd = $oldPassword";
		
		$temp = $this->db->fetchOne($sql);
		if($temp != 1) {
			$errors[] = __("Det indtastede gamle kodeord er ikke gyldigt.");
			return $errors;
		}
		return $this->setPassword($cid,$data['password']);
	}
	
	/**
	* insist is used to overrule previous reset of passwords, that are still active
	* ie. the reset_password_minutes has not elapsed
	* Returns true if the password is ready to be reset and mail has been send
	*/
	public function resetPassword($email, $insist = false) {
		$errors = array();
		$qEmail = $this->db->quote($email);
		
		############# GET CID
		$sql = 'SELECT * FROM `customers` WHERE ' .
			'`email` = ' . $qEmail;
		$custRow = $this->db->getRow($sql);
		if(!$custRow) {
			if($email != '')
			{
				$errors[] = __("Der er ingen brugere med e-mailen: {%email}.", array('email'=>$email));
			}
			else
			{
				$errors[] = __("Der er ikke indtastet nogen e-mail");
			}
			return $errors;
		}
		$cid = $custRow->id;
		$qCid = $this->db->quote($cid);
		$name = $custRow->name;
		
		
		####### Check active resets for user
		$now = date('Y-m-d H:i:s');
		$earliestStart = new DateTime($now);
		$earliestStart->sub(new DateInterval("PT".Settings::get('reset_password_minutes')."M"));//date_sub($now,date_interval_create_from_date_string( . ' minutes'));
		$date = $this->db->quote($earliestStart->format('Y-m-d H:i:s'));
		$sql = 'SELECT `reset_date` FROM `reset_pwd` WHERE ' .
			'`customer_id` = ' . $qCid . ' AND ' .
			'`reset_date` >= ' . $date;
			
		$result = $this->db->fetchOne($sql);
		if(!isset($result) || $insist) {
			########### DELETE OLD -- THERE MIGHT BE AN UNUSED RESETER
			$this->db->beginTransaction();
			$sql = 'DELETE FROM `reset_pwd` WHERE ' .
				'`customer_id` = ' . $qCid;
			$num = $this->db->exec($sql);
			if($num > 1) {
				$this->db->rollback();
				$errors[] = "DB error, deleted more than one reset_column -- rolling back. This should not be possible due to unique customer id";
				return $errors;
			}
			$this->db->commit();
			
			################ ADD NEW
			$code = $this->generateCodeToReset($cid);
			$qCode = $this->db->quote($code);
			$sql = 'INSERT INTO `reset_pwd`(`customer_id` , `reset_code`) VALUES (' .
				"$qCid , $qCode )";
			
			$this->db->beginTransaction();
			$num = $this->db->exec($sql);
			if($num != 1) {
				$this->db->rollback();
				$errors[] = "DB error, kunne ikke tilføje reset session -- rolling back.";
				return $errors;
			}
			$this->db->commit();
			
			############# SEND EMAIL
			if(!VublaMailer::sendResetCodeEmail($email,$code,$name)) {
				$errors[] = __("Kunne ikke sende e-mail, men kodeordet er klar til at blive nulstillet -- Vær venlig at kontakt os");
				return $errors;
			}
			
			return;
		}
		
		$errors[] = __("Der er allerede en aktiv nulstillings session -- denne var startet: ") . $result;
		return $errors;
	}
	
	public function validateResetPassword($email,$code,$password,$password2) {
		$errors = array();
		if($password != $password2) {
			$errors[] = __("Kodeordene stemmer ikke overens");
			return $errors;
		}
		$temp = $this->validateEmailCodeCombination($email,$code);
		if(!empty($temp)) {
			return $temp;
		}
		$qEmail = $this->db->quote($email);
		$sql = 'SELECT * FROM `customers` WHERE ' .
			'`email` = ' . $qEmail;
		$custRow = $this->db->getRow($sql);
		if(!$custRow) {
			$errors[] = __("Der er ingen brugere med e-mailen: {%email}.", array('email'=>$qEmail));
			return $errors;
		}
		$cid = $custRow->id;
		$qCid = $this->db->quote($cid);
		
		
		
		
		############# SET PASSWORD
		try
		{
			if(!self::setPassword($cid,$password)) {
				throw new VublaException(__("Det nye password bliv ikke sat"));
			}
		}
		catch(Exception $e)
		{
			$errors[] = $e->getMessage();
			return $errors;
		}
		
		############# REMOVE ENTRY IN RESET_PWD
		$sql = 'DELETE FROM `reset_pwd` WHERE ' .
			"`customer_id` = $qCid";
		
		$this->db->beginTransaction();
		$num = $this->db->exec($sql);
		if($num != 1) {
			$this->db->rollback();
			$errors[] = "DB error, kunne ikke fjerne den korrekte reset session -- rolling back.";
			return $errors;
		}
		$this->db->commit();
		
		############# ??SEND CONFIRMATION MAIL??
	}
    
    public function setEmail($email, $password = null){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return __('Ugyldig email');
        }
        $db = VPDO::getVdo(DB_METADATA);
        $sql = 'update customers set email = ? where email = ?';
        $db->beginTransaction();
        $stm = $db->prepare($sql);
        $stm->execute(array($email, $_SESSION['email']));
        $stm->closeCursor();  
        if(!is_null($password) && strlen($password) > 0){
            try {           
                $this->setPassword($_SESSION['uid'], $password, false);// true for in transaction
            } catch(VublaException $e){
                $db->rollBack(); 
                return $e->getMessage();
            } 
        }
        $db->commit();
        $_SESSION['email'] = $email;
        return true;
    }
	private function setPassword($cid,$newPassword, $notInTrans = true) { // not in transaction
		$id = $this->db->quote($cid);

		###### CHECKING THAT THE CURRENT USER EXISTS, MIGHT BE UNNECESSARY
		$sql = "SELECT email FROM customers WHERE " .
			"id = $id";
		
		$temp = $this->db->fetchOne($sql);
		if(!isset($temp)) {
			throw new VublaException("Invalid user id");
		}
		
		if(strlen($newPassword) < 7) {
			throw new VublaException("Password for kort");
		}
		else {		
            $passwordNew = $this->db->quote(md5($newPassword.self::$salt));
		}
		
		
		$sql = "UPDATE customers " .
			"SET pwd = $passwordNew WHERE " .
			"id = $id";
			
		$this->db->condBeginTransaction($notInTrans);

		$num = $this->db->exec($sql);
		if($num != 1) {
			if($num == 0) {
				############# TRYING TO RESET TO THE SAME???
				$sql = "SELECT email FROM `customers` WHERE " .
					"`pwd` = $passwordNew AND " .
					"`id` = $id";
				$temp = $this->db->fetchOne($sql);
				if($temp) {
					//The password is reset(to the same as it was before though)
					$this->db->condCommit($notInTrans);
					return true;
				} else {
					$this->db->rollback();
					throw new VublaException("DB error, kunne ikke gemme det nye kodeord, muligvis mangle DB rettigheder eller customer er slettet -- rolling back");
				}
			}
			else
			{
				$this->db->rollback();
				throw new VublaException("DB error, kunne ikke gemme det nye kodeord -- rolling back");
			}
		}
		
		$this->db->condCommit($notInTrans);
		
		return true;
	}
	
	public function validateEmailCodeCombination($email,$code) {
		############ VERIFY EMAIL AND GET CID
		$qEmail = $this->db->quote($email);
		$sql = 'SELECT * FROM `customers` WHERE ' .
			'`email` = ' . $qEmail;
		$custRow = $this->db->getRow($sql);
		if(!$custRow) {
			$errors[] = __("Der er ingen brugere med e-mailen: {%email}.", array('email'=>$qEmail));
			return $errors;
		}
		$cid = $custRow->id;
		$qCid = $this->db->quote($cid);
		$qCode = $this->db->quote($code);
		##########  VERIFY TIME
		$now = date('Y-m-d H:i:s');
		$earliestStart = new DateTime($now);
		$earliestStart->sub(new DateInterval("PT".Settings::get('reset_password_minutes')."M"));//date_sub($now,date_interval_create_from_date_string( . ' minutes'));
		$date = $this->db->quote($earliestStart->format('Y-m-d H:i:s'));
		$sql = 'SELECT * FROM `reset_pwd` WHERE ' .
			"`customer_id` = $qCid AND " .
			"`reset_date` >= $date";
		$result = $this->db->fetchOne($sql);
		if(!isset($result)) {
			$errors[] = __('Der er ingen aktiv nulstilling igang for denne e-mail -- prøv at nulstille adgangskoden igen');
			return $errors;
		}

		############ VERIFY CODE
		$sql = 'SELECT * FROM `reset_pwd` WHERE ' .
			"`customer_id` = $qCid AND " .
			"`reset_code` = $qCode";
		$result = $this->db->fetchOne($sql);
		if(!isset($result)) {
			$errors[] = __('Invalid code -- Den code der er tilknyttet med dette link stemmer ikke overens med den vi er i besidelse af. Prøv at nulstille dit kodeord igen.');
			return $errors;
		}
		
		return;
	}
	
	public function resendActivationLink($email) {
		$qEmail = $this->db->quote($email);
		$sql = 	"SELECT * FROM `customers` WHERE ".
				"email = $qEmail";
		
		$result = $this->db->getRow($sql);
		if($result)	{
			if($result->email_activated == 0) {
				if(VublaMailer::sendActivationEmail($qEmail)) {
					return; //Success
				}
				return array(VublaMailer::$lastError);
			}
			return array(__("E-mailen: {%email} er allerede aktiveret.", array('email'=>$email)));
		}
		return array(__("Ukendt e-mail: "). $email);
	}
	
	public function delete($cid,$sendConfirmEmail = true) {
		$wid = self::getWid($cid);
		$this->db->beginTransaction();
		################ INSERT CUSTOMER INTO deleted_customers
		$qCid = $this->db->quote($cid);
		$sql = 	"INSERT INTO deleted_customers (id,wid,email,pwd,session,cookie,ip,name,company,address,address2,postal,city,phone,email_activated,delete_time,soap_type,master_id,on_config)" .
				"SELECT id,wid,email,pwd,session,cookie,ip,name,company,address,address2,postal,city,phone,email_activated," . time() . ",soap_type,master_id,on_config " .
				"FROM customers " .
				"WHERE customers.id = " . $qCid;
		
		$result = $this->db->exec($sql);
		if($result != 1) {
			$this->db->rollback();
			return array("Kunne ikke indsætte brugeren med id " . $cid . " i deleted_customers". print_r($this->db->errorInfo(),true));
		}
		
		
		########### DELETE FROM customers
		$sql =	"DELETE FROM customers ".
				"WHERE id = " . $qCid;
				
		$result = $this->db->exec($sql);
		if($result != 1) {
			$this->db->rollback();
			return array("Kunne ikke slette brugeren med id " . $cid . " fra customers");
		}
        
        ################ INSERT WEBSHOP INTO deleted_webshops
        $qWid = $this->db->quote($wid);
        $sql =  "INSERT INTO deleted_webshops (id,name,hostname,type,enabled,seckey,debug,pack_id,next_pack_id,paydate,transactionnum,is_trial,test_shop) " .
                "SELECT id,name,hostname,type,enabled,seckey,debug,pack_id,next_pack_id,paydate,transactionnum,is_trial,test_shop " .
                "FROM webshops " .
                "WHERE webshops.id = " . $qWid;
        
        $result = $this->db->exec($sql);
        if($result != 1) {
            $this->db->rollback();
            return array("Kunne ikke indsætte webshoppen med id " . $wid . " i deleted_webshops");
        }
        
        
        ########### DELETE FROM customers
        $sql =  "DELETE FROM webshops ".
                "WHERE id = " . $qWid;
                
        $result = $this->db->exec($sql);
        if($result != 1) {
            $this->db->rollback();
            return array("Kunne ikke slette webshopen med id " . $wid . " fra webshops");
        }
		
		############# DISABLE VUBLA
        try{
		  Settings::setLocal("enabled","0",$wid);
		} catch(PDOException $e) {
			$this->db->rollback();
            return array("Kunne ikke slå Vubla fra for den bruger med id: " . $cid . " -- er databasen fjernet?");
        }
		############# REMOVE FROM CRAWL LIST
		$qWid = $this->db->quote($wid);
		$sql =	"DELETE FROM crawllist ".
				"WHERE wid = " . $qWid;
		
		//TODO: Check that it is not being crawled
		
		$result = $this->db->exec($sql);
		if($result > 1) {
			$this->db->rollback();
			return array("Deleted too many entries from the crawllist -- rolling back");
		}
        
        #################### REMOVE FROM RESET PASSWORD
        
		$q = "DELETE FROM reset_pwd WHERE customer_id = $qCid";
		$result = $this->db->exec($q);
		if($result > 1) {
			$this->db->rollback();
			return array("Deleted too many entries from the reset_pwd -- rolling back");
		}
        
        ################# REMOVE FROM PAYMENTS
        $sql =  "DELETE FROM payments ".
                "WHERE wid = $qWid";
        $result = $this->db->exec($sql);
        if($result > 1) {
            $this->db->rollback();
            return array("Deleted more than one entry in payments -- rolling back");
        }
        $this->db->commit();
        
        //Check that it is deleted:
        $sql =  "SELECT id ".
                "FROM payments ".
                "WHERE wid = $qWid";
        
        $result = $this->db->fetchOne($sql);
        if($result){
            return array("Brugen blev ikke slettet fra betalinger, tag venligst kontakt til os");
        }
		
		########### SEND MAIL
		$sql =	"SELECT name, email ".
				"FROM deleted_customers ".
				"WHERE id = ". $qCid;
		
		$result = $this->db->getRow($sql);
		$name = $result->name;
		$email = $result->email;
        if($sendConfirmEmail) {
    		if(!VublaMailer::sendDeletedConfirmation($email,$name)) {
    			return array("Brugeren blev slettet, men der kunne ikke sendes nogen e-mail");
    		}
		}
	}
    
    public function purge($cid) {
		$qCid = $this->db->quote($cid);
		$wid = $this->db->fetchOne("SELECT wid FROM deleted_customers WHERE deleted_customers.id = $qCid");
        if(is_null($wid)) {
			$wid = $this->db->fetchOne("SELECT wid FROM customers WHERE customers.id = $qCid");
            if(is_null($wid)) {
                return array("Unable to find user with id '" . $cid . "' in deleted_customers or customers");
            }
        }
		
		########### DELETE DB
        $dbman = new WebshopDbManager();
        $dbman->delete($wid);
	}
	
	//Recover a user from deletion
	public function recover($cid) {
        
        ########### INSERT CUSTOMER INTO customers
        $qCid = $this->db->quote($cid);
        $sql =  "INSERT INTO customers (id,wid,email,pwd,session,cookie,ip,name,company,address,address2,postal,city,phone,email_activated,soap_type,master_id)" .
                "SELECT id,wid,email,pwd,session,cookie,ip,name,company,address,address2,postal,city,phone,email_activated,soap_type,master_id " .
                "FROM deleted_customers " .
                "WHERE id = " . $qCid;
        
        $result = $this->db->exec($sql); 
        if($result != 1) {
            return array("Kunne ikke indsætte brugeren med id " . $cid . " i customers");
        }
        
        
        ########### DELETE FROM deleted_customers
        $sql =  "DELETE FROM deleted_customers ".
                "WHERE id = " . $qCid;
                
        $result = $this->db->exec($sql);
        if($result != 1) {
            return array("Kunne ikke slette brugeren med id " . $cid . " fra deleted_customers");
        }
		
		############# INSERT INTO CRAWL LIST
		$wid = self::getWid($cid);
		$qWid = $this->db->quote($wid);
		$sql =	"INSERT INTO crawllist (wid,last_crawled,currentlybeingcrawled,email_me) ".
				"VALUES ($qWid,0,0,0)";
		
		$result = $this->db->exec($sql);
		if($result != 1) {
			return array("Kunne ikke indsætte i crawllist." , $this->db->errorInfo());
		}
		
        
        ########### INSERT WEBSHOP INTO webshops
        $qWid = $this->db->quote($wid);
        $sql =  "INSERT INTO webshops (id,name,hostname,type,enabled,seckey,debug,pack_id,next_pack_id,paydate,transactionnum,is_trial,test_shop)" .
                "SELECT id,name,hostname,type,enabled,seckey,debug,pack_id,next_pack_id,paydate,transactionnum,is_trial,test_shop " .
                "FROM deleted_webshops " .
                "WHERE id = " . $qWid;
        
        $result = $this->db->exec($sql); 
        if($result != 1) {
            return array("Kunne ikke indsætte webshoppen med id " . $wid . " i webshops");
        }
        
        
        ########### DELETE FROM deleted_webshops
        $sql =  "DELETE FROM deleted_webshops ".
                "WHERE id = " . $qWid;
                
        $result = $this->db->exec($sql);
        if($result != 1) {
            return array("Kunne ikke slette webshoppen med id " . $Wid . " fra deleted_webshops");
        }
		
	}
    
    public function updateCustomerData($cid,$data) {
        $errors = array();
		$dbadmin = new WebshopDbManager();
        $sql = "UPDATE customers SET ";
    
            if(strlen($data['name']) > 3){
                $name = $this->db->quote(strip_tags($data['name']));
                $sql .= "`name` = $name, ";
            } else {
                $errors['name'] = __('Ugyldigt navn. Husk at det skal være dit fulde navn.');
            }
        //}
		
		##### Company ####
        //if(!empty($data['company'])){
            $company = $this->db->quote(strip_tags($data['company']));
            $sql .= "`company` = $company, ";
        //}
        
        ##### Phone ####
        //if(!empty($data['phone'])){
            if(strlen($data['phone']) > 3){
                $phone = $this->db->quote(strip_tags($data['phone']));
                $sql .= "`phone` = $phone, ";
            } else {
                $errors['phone'] = __('Ugyldigt telefon nummer');
            }
        //}
        
        ##### Adress ####
        //if(!empty($data['address'])){
            if(strlen($data['address']) > 3){
                $address = $this->db->quote(strip_tags($data['address']));
                $sql .= "`address` = $address, ";
            } else {
                $errors['address'] = __('Ugyldig adresse.');
            }
        //}
        
        ##### Adress2 ####
        //if(!empty($data['address2'])){
            $address2 = $this->db->quote($data['address2']);
            $sql .= "`address2` = $address2, ";
        //}

        
        ##### Postal ####
        //if(!empty($data['postal'])){
            if(strlen($data['postal']) < 20){
                $postal = $this->db->quote(strip_tags($data['postal']));
                $sql .= "`postal` = $postal, ";
            } else {
                $errors['postal'] = __('Ugyldigt postnummer.');
            }
        //}
        
        ##### Postal ####
        //if(!empty($data['postal'])){
            if(ctype_digit($data['country_id'])){
                $country_id = $this->db->quote(strip_tags($data['country_id']));
                $sql .= "`country_id` = $country_id, ";
            } else {
                $errors['country_id'] = __('Ugyldigt land.');
            }
        //}
        
        ##### City ####
        //if(!empty($data['city'])){
            if(strlen($data['city']) > 1){
                $city = $this->db->quote(strip_tags($data['city']));
                $sql .= "`city` = $city, ";
            } else {
               $errors['city'] = __('Ugyldig by.');
            }
        //}
        
        ##### Password ####
        if(!empty($data['password']) || !empty($data['password2']) || !empty($data['oldPassword'])){
            $res = $this->changePassword($cid,$data);
            if($res === false )
            {
                $errors['password'] = __("Alle kodeordsfelter var ikke udfyldt");
            }
            if(is_string($res[0]))
            {
                $errors['password'] = $res[0];
            }
        }

		##### Add to database ####		
		try {
			if(empty($errors)) {
                $sql = substr($sql,0,strlen($sql)-2);
                $cid = $this->db->quote($cid);
                $sql .= " WHERE `id` = $cid";
                $this->db->beginTransaction();
                if($this->db->exec($sql) > 1) {
                    $this->db->rollback();
                    return array("More than one entry was updated -- rolling back");
                }
                //var_dump($sql);exit;
                $this->db->commit();
				
				if(!empty($email) && !VublaMailer::sendActivationEmail($email)) {
					return array("Could not send E-Mail but update was completed - Please contact us");
				}
	
				return '';
			}
			else {
				return $errors;
			}
		} catch(VublaException $e){
			ob_clean();
			echo __('Vi beklager der skete en fejl. Vi arbejder på sagen');
			if(VUBLA_DEBUG){
				var_dump($e);
			}
			   
			exit;
		}
    }
    
    public function getCustomerFromSingleField($column,$value) {
        $qColumn = '`'.$column.'`';
        $qValue = $this->db->quote($value);
        $sql = 'SELECT * FROM `customers` WHERE ' .
            $qColumn. ' = ' . $qValue;
        return $this->db->getRow($sql);
    }
    
    public function getDeletedCustomerFromSingleField($column,$value) {
        $qColumn = '`'.$column.'`';
        $qValue = $this->db->quote($value);
        $sql = 'SELECT * FROM `deleted_customers` WHERE ' .
            $qColumn. ' = ' . $qValue;
        $result =$this->db->getRow($sql);
        return $result;
    }
    
    public function getCustomerData($cid) {
	   $q = 'SELECT name,phone,company,address,address2,postal, city, country_id FROM customers WHERE id = ' . $this->db->quote($cid);
	   $result = $this->db->getRow($q);
       return $result;
    }
    
    public function getCustomerDataWithEmail($cid) {
	   $q = 'SELECT name,email,postal,address,address2,company,city, country_id,phone,type FROM customers WHERE id = ' . $this->db->quote($cid);
	   $result = $this->db->getRow($q);
       return $result;
    }
    
    public function crawlAtNextCron($cid,$emailMe = false) {
        if($emailMe){
            $emailMe = $this->db->quote('1');
        } else {
            $emailMe = $this->db->quote('0');
        }
        $wid = $this->db->quote(self::getWid($cid));
        $sql = 'update crawllist set scrape_asap = 1, email_me = '.$emailMe.', currentlybeingcrawled = 0 where wid = '.$wid;
        $this->db->beginTransaction();
        if($this->db->exec($sql) == 1) {
            $this->db->commit();
            return true;
        }
        $this->db->rollback();
        return false;
    }
    
    public static function getAllTestUsers() {        
        return self::getAllUsers(array('test_shop'=>'1'));
    }
    
    public static function getAllNonTestUsers() {        
        return self::getAllUsers(array('test_shop'=>'0'));
    }
    
    public static function getAllUsers($conditions = null) {
        $result = array();
        $sqlConditions = ' ';
        $db = VPDO::getVDO(DB_METADATA);
        if(is_array($conditions))
        {
            foreach ($conditions as $key => $value) {
                $sqlConditions .= ' and `'.$key.'` = '.$db->quote($value);
            }
        }
        $sql =  "SELECT w.*,c.*,round(case (w.test_shop) when    1 then '0' when 0 then (prices.value ) end ) as price , c.name as real_name " .
                "FROM customers c inner join webshops w on c.wid = w.id left join subscription_packages sp on w.pack_id = sp.id left join prices using(price_id) 
                where (prices.language_id = 2 or  prices.language_id is null)".
                $sqlConditions;
        $list = $db->getTableList($sql);
		
		return $list;
    }
    
	public static function getAllDeletedUsers() {
        $result = array();
        $sql =  "SELECT * " .
                "FROM deleted_customers";
		$db = VPDO::getVDO(DB_METADATA);
        $list = $db->getTableList($sql);
		
		return $list;
    }
    
    
    public function getMasterId($cid) {
        $sql =  "SELECT master_id " .
                "FROM `customers` ".
                "WHERE id = " . $this->db->quote($cid);
        $result = $this->db->fetchOne($sql);
        
        return $result;
    }
    
    
    public function getMasterIdOfDeleted($cid) {
        $sql =  "SELECT master_id " .
                "FROM `deleted_customers` ".
                "WHERE id = " . $this->db->quote($cid);
        $result = $this->db->fetchOne($sql);
        
        return $result;
    }
    
	private function generateCodeToReset($cid) {
		$cid = $this->db->quote($cid);
		$sql = "SELECT * FROM customers WHERE " .
    		"id = $cid";

    	$result = $this->db->getRow($sql);

		if(is_object($result))
		{
			return md5(time().$result->pwd.$result->email.self::$salt);
		}
		else
		{
			return null;
		}
	}
	
	private static function hashPassword($password) {
		return md5($password.self::$salt);
	}
}
?>
