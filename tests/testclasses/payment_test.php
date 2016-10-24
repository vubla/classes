<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';


new PHPUnit_Framework_TestSuite("PaymentTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class QuickPay {
    static $vars;
    static $qpstat = '000';
    public static $e = null;
    function commit(){
        self::$vars = get_object_vars($this);
        return array('qpstat'=>self::$qpstat,'qpstatmsg'=>"eeeee", 'ordernumber'=>'PHPUNITTEST'.self::$e,
            'chstat'=>'000', 'chstatmsg'=>"1", "merchant"=>1,"merchantemail"=>"someemail","transaction"=>"ddd",
            "cardtype"=>"sss", "cardnumber"=>"sdssd", "cardexpire"=>"2222","splitpayment"=>1, "md5check"=>"wert", 
            "fraudprobability"=>1, "fraudremarks"=>"1","fraudreport"=>"wwer",
            'amount'=>234, 'msgtype'=>'recurring','currency'=>"DKK", "time"=>"000000", 'state'=>"1");
    }
    
}
/*
class Invoice extends FPDF {

    function createInvoice($invoice_nr,$date,$company,$address,$purchases,$currency) {}
    
    function Header() {}
    
    function Footer() {}
    
    function createInvoiceInfoTable() {}
    
    function createDataTable($th,$data){}
}
*/

class PaymentTest extends BaseDbTest 
{
    public $data;
    protected $wid = 3;
    private $ob = false;
    
    function setUp() {
        $this->buildDatabases();
        $this->vdo = VPDO::getVdo(DB_METADATA);
     Settings::setGlobal('info_email_address', 'info@vubla.com');
        Settings::setGlobal('admin_language',1);
        QuickPay::$qpstat = '000';
        ob_start();
        QuickPay::$vars = array();
    }
    
    function tearDown() {
       
        $this->dropDatabases();
        ob_end_clean();
    }
    
    function testProcess(){
      
         Settings::setLocal('admin_language',1, 3); // Set it to danish
        $this->vdo->exec($this->getPaymentsLogSql());
        $org_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $object = new Payment(3);   
        QuickPay::$e = "da";
        
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 1 where id = 3');
        $object->process();
        
        $this->assertEquals('12375',QuickPay::$vars['amount']);
        
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 2 where id = 3');
        $object->process();
        $this->assertEquals('37375',QuickPay::$vars['amount']);
        $this->assertEquals('DKK',QuickPay::$vars['currency']);
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 3 where id = 3');
        QuickPay::$e = "da";
        $object->process();
        $this->assertEquals('124875',QuickPay::$vars['amount']);
       
        $res = $this->vdo->fetchOne('select pack_id from webshops where id = 3');
        $this->assertEquals(3, $res);
        
        $new_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $delay_buffer = 50;
        $this->assertEquals($org_paydate+ (30*24*60*60)*3, $new_paydate ,"org was $org_paydate and Diif " .($org_paydate+ (30*24*60*60)-$new_paydate ) );
        // We multiply by three because we process trice. 
       
          QuickPay::$e = "da"; 
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 2 where id = 3');
        $this->vdo->exec('update prices set value = 0 ');
        $object->process();
       // ob_end_clean();
        $res = $this->vdo->fetchOne('select pack_id from webshops where id = 3');
        $this->assertEquals(2, $res);
         $this->assertEquals('124875',QuickPay::$vars['amount']); // We can only assert that it is unchanged from last test
        
    }
    
    function testProcessEn(){
         Settings::setLocal('admin_language',2, 3); // Set it to english
        Language::reset();
        QuickPay::$e = "en";
        $this->vdo->exec($this->getPaymentsLogSql());
        $org_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $object = new Payment(3);
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 1 where id = 3');
        $object->process();
        $this->assertEquals('2375',QuickPay::$vars['amount']);
        QuickPay::$e = "en";
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 2 where id = 3');
        $object->process();
        $this->assertEquals('6125',QuickPay::$vars['amount']);
        $this->assertEquals('USD',QuickPay::$vars['currency']);
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 3 where id = 3');
        
        QuickPay::$e = "en";
        $object->process();
        $this->assertEquals('24875',QuickPay::$vars['amount']);
       
        $res = $this->vdo->fetchOne('select pack_id from webshops where id = 3');
        $this->assertEquals(3, $res);
        
        $new_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $delay_buffer = 50;
        $this->assertEquals($org_paydate+ (30*24*60*60)*3, $new_paydate ,"org was $org_paydate and Diif " .($org_paydate+ (30*24*60*60)-$new_paydate ) );
        // We multiply by three because we process trice. 
       //    ob_end_clean();
    }
    
    function testProcessFAIL(){
          Settings::setLocal('admin_language',1, 3); 
      //   ob_start();
        Language::reset();
        QUICKPAY::$qpstat = '001';
        $paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $this->vdo->exec($this->getPaymentsLogSql());
        $object = new Payment(3);
  
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 3 where id = 3');
        $object->process();
        $this->assertEquals('124875',QuickPay::$vars['amount']);
       
        $res = $this->vdo->fetchOne('select pack_id from webshops where id = 3');
        $this->assertEquals(1, $res);
        
        $res = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $this->assertEquals($paydate, $res);
      //s   ob_end_clean();
    }
    
    function testNewVAT(){
      
         Settings::setLocal('admin_language',1, 3); // Set it to danish
         Settings::setLocal('payment_vat',1, 3);
        $this->vdo->exec($this->getPaymentsLogSql());
        $org_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $object = new Payment(3);   
        QuickPay::$e = "danovat";
        
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 1 where id = 3');
        $object->process();
        
        $this->assertEquals('9900',QuickPay::$vars['amount']);
        
        $new_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $this->assertEquals($org_paydate+ (30*24*60*60), $new_paydate ,"org was $org_paydate and Diif " .($org_paydate+ (30*24*60*60)-$new_paydate ) );
    }
    
    function testNewVATEn(){
      
         Settings::setLocal('admin_language',2, 3); // Set it to english
          Language::reset();
         Settings::setLocal('payment_vat',1, 3);
        $this->vdo->exec($this->getPaymentsLogSql());
        $org_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $object = new Payment(3);   
        QuickPay::$e = "ennovat";
        
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 1 where id = 3');
        $object->process();
        
        $this->assertEquals('1900',QuickPay::$vars['amount']);
        
        $new_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $this->assertEquals($org_paydate+ (30*24*60*60), $new_paydate ,"org was $org_paydate and Diif " .($org_paydate+ (30*24*60*60)-$new_paydate ) );
    }
    
    
    function testSuspiciousVAT(){
      
         Settings::setLocal('admin_language',1, 3); // Set it to danish
         Settings::setLocal('payment_vat',123, 3);
        $this->vdo->exec($this->getPaymentsLogSql());
        $org_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $object = new Payment(3);   
        QuickPay::$e = "suspeciousvat";
        
        $this->vdo->exec('update webshops set pack_id = 1, next_pack_id = 2 where id = 3');
        $object->process();
        
        $this->assertArrayNotHasKey('amount',QuickPay::$vars);
        
        $new_paydate = $this->vdo->fetchOne('select paydate from webshops where id = 3');
        $this->assertEquals($org_paydate, $new_paydate ,"org was $org_paydate and Diif " .($org_paydate-$new_paydate ) );
    }
    
  function getPaymentsLogSql(){
     
return '
CREATE TABLE IF NOT EXISTS `payments_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wid` int(11) NOT NULL,
  `pids` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `msgtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ordernumber` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `time` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `state` int(2) NOT NULL,
  `qpstat` int(3) NOT NULL,
  `qpstatmsg` text COLLATE utf8_unicode_ci NOT NULL,
  `chstat` int(3) NOT NULL,
  `chstatmsg` text COLLATE utf8_unicode_ci NOT NULL,
  `merchant` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `merchantemail` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `transaction` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `cardtype` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `cardnumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `cardexpire` int(4) NOT NULL,
  `splitpayment` int(1) NOT NULL,
  `md5check` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `fraudprobability` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fraudremarks` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fraudreport` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
update subscription_packages set prices = 0; 

';
  }
}



