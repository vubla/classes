<?php



require_once '../vublamailer.php';
require_once '../basedbtest.php';

// The word will not run on windows.. ENCODING!!!!
if (stristr(PHP_OS, 'WIN')) { 
 $suite  = new PHPUnit_Framework_TestSuite("WordTestWin");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

//define('HOST', 'db.vubla.com');
class WordTestWin extends BaseDbTest 
{
    function testDummy1() {
        parent::testDummy();
    }
}



} else {

$suite  = new PHPUnit_Framework_TestSuite("WordTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

//define('HOST', 'db.vubla.com');
class WordTest extends BaseDbTest 
{
    
        function tearDown()
        {
            $vdo = vdo::webshop(1);
            $vdo->exec(" drop database if exists " . DB_PREFIX."1");
          
        }
        function testRemoveEnds()
        {
            
          /*     $subject = new word("smoething". chr(162));
            $subject->removeEnds();
            var_dump($subject);
                
              echo preg_replace('/A/', 'H', "smoíAæøåethingl". chr(162)) ;
           
          echo PHP_EOL;
          $w = word::getNonAllowableChars();
          echo bin2hex("h") . PHP_EOL;
          foreach ($w as $key => $value) {
              e9cho bin2hex($value) . PHP_EOL;
          }
           * */
        
           $this->createDb("");    
           $this->_testRemoveEnds("íæ");
           $this->_testRemoveEnds("pen");
           $this->_testRemoveEnds("peN", "pen");
           $this->_testRemoveEnds("hår");
           $this->_testRemoveEnds("hÆØÅr","hæøår");
           $this->_testRemoveEnds("en");
           $this->_testRemoveEnds("æøåó");
           $this->_testRemoveEnds("üÿÿ");
           $this->_testRemoveEnds(("smoething". chr(162)), "smoething");
          // echo PHP_EOL;
          
        }
        
        
        function _testRemoveEnds($word, $out = null)
        {
            if(is_null($out))
            {
                $out = $word;
            }
            $subject = new word($word);
            $subject->removeEnds();
            $this->assertEquals($out, $subject->short, $word);
            $this->assertEquals(bin2hex($out), bin2hex($subject->short), $word);
        }
        
         function testTheStrangeE()
        {
            
           $subject = new word("somé");
            $subject->removeEnds();
            $this->assertEquals("some", $subject->short);
            $this->assertEquals(bin2hex("some"), bin2hex($subject->short));
          // echo PHP_EOL;
          
        }
        
        function testSaveWord()
        {
          Scraper::$static_wid = 1;
                
            $enc = "COLLATE utf8_unicode_ci";
            $this->createDb($enc);
            $subject = new word("pasta");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            
        }
        
        function testSaveWordBordskrabee()
        {
          Scraper::$static_wid = 1;
                
            $enc = "COLLATE utf8_unicode_ci";
            $this->createDb($enc);
            $subject = new word("vægskraber");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            
        }
       
        function testSaveWord1()
        {
          
            $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("pasta");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
        }
        
        function testSaveWordBedEnc()
        {
          
            $enc = "COLLATE utf8_unicode_ci";
           
            $this->createDb($enc);
            $subject = new word("hår");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
        }
        
        
        
         function testSaveWordBedEnc2()
        {
          
            $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("hår");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
            $this->assertEquals(1 ,$this->at);
        }


        function testSaveWordBedEnc3()
        {
          
            $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("ón");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
            $this->assertEquals(1 ,$this->at);
        }
        
        function testSaveWordBedEnc4()
        {
          
            $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("penner");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
            $this->assertEquals(1 ,$this->at);
        }
        
        
        function testSaveWordBedEnc5()
        {
          
           $enc = "COLLATE utf8_unicode_ci";
           
            $this->createDb($enc);
            $subject = new word("ón");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation($subject));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation($subject,2));
            $this->assertEquals(1 ,$this->at);
        }
        
        function testSaveWordBedEnc6()
        {
          
               $enc = "COLLATE utf8_unicode_ci";
           
            $this->createDb($enc);
            $subject = new word("pen'    ");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation("pen"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("pen",2));
            $this->assertEquals(1 ,$this->at);
        }
        
           function testSaveWordBedEncHair()
        {
          
             $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("har");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation("har"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("har",2));
            $this->assertEquals(1 ,$this->at);
            $subject = new word(("anton"));
            
            $subject->save(1,"inname");
           $subject = new word(("æblegrød"));
            $subject->save(1,"inname");
            
            $subject = new word(("hårpleje"));
            $subject->save(1,"inname");
            $subject = new word(("hår"));
            $subject->save(1,"inname");//exit;
            $this->assertTrue($this->hasRelation("hår"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("hår",2));
            $this->assertEquals(1 ,$this->at);
    //        exit;
              $vdo = vdo::webshop(1);
             $res = $vdo->getTableList("select * from words_tmp");
           // var_dump($res);
        }
        
        
        function testSaveWordBedEncHair2()
        {
          
               $enc = "COLLATE utf8_unicode_ci";
           
            $this->createDb($enc);
            $subject = new word("har");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation("har"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("har",2));
            $this->assertEquals(1 ,$this->at);
            $subject = new word(("anton"));
            
            $subject->save(1,"inname");
           $subject = new word(("æblegrød"));
            $subject->save(1,"inname");
            
            $subject = new word(("hårpleje"));
            $subject->save(1,"inname");
            $subject = new word(("hår"));
            $subject->save(1,"inname");//exit;
            $this->assertEquals("har",$this->hasRelation("hår"));
            $subject->save(2,"inname");
              $this->assertEquals("har",$this->hasRelation("hår",2));
            $this->assertEquals(3 ,$this->at);
    //        exit;
              $vdo = vdo::webshop(1);
             $res = $vdo->getTableList("select * from words_tmp");
            //var_dump($res);
        }
        
           function testSaveWordBedEncAndevelse()
        {
             Scraper::$static_wid = 1;
          VOB::setTarget(VOB::TARGET_STDOUT);
             $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("anven-d-elsesmetode");
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation("anven-d-elsesmetode"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("anven-d-elsesmetode",2));
            $this->assertEquals(1 ,$this->at);
           
        }
    
        function testSaveWordBedEncCharXA2()
        {
             Scraper::$static_wid = 1;
          VOB::setTarget(VOB::TARGET_STDOUT);
             $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("smoething". chr(162));
            $subject->save(1,"inname");
            $this->assertTrue($this->hasRelation("smoething"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("smoething",2));
            $this->assertEquals(1 ,$this->at);
           
        }
        
        function testSaveWordBedEncNutrition()
        {
             Scraper::$static_wid = 1;
          VOB::setTarget(VOB::TARGET_STDOUT);
             $enc = "CHARACTER SET utf8 COLLATE utf8_bin";
           
            $this->createDb($enc);
            $subject = new word("íæ");
          
            $subject->removeEnds();
            $this->assertEquals("íæ", $subject->short);
            $this->assertEquals(bin2hex("íæ"), bin2hex($subject->short));
            $subject->save(1,"inname"); 
        //   exit;
            $this->assertTrue($this->hasRelation("íæ"));
            $subject->save(2,"inname");
            $this->assertTrue($this->hasRelation("íæ",2));
            $this->assertEquals(1 ,$this->at);
           
        }
        function hasRelation($word, $product = 1)
        {
            $vdo = vdo::webshop(1);
            //$count = $vdo->fetchOne("select word from word_relation_tmp wr inner join words_tmp w on w.id = wr.word_id where word  = ?  and product_id = ? ", array($word, $product));
            $at = 1;
         //   if($count != $word)
            {
               $count = $vdo->fetchOne("select word from word_relation_tmp wr inner join words_tmp w on w.id = wr.word_id where word like ? and product_id = ? ", array($word, $product));
         //    $at = 2;
            }
            if($count != $word)
            {
             //  $count = $vdo->fetchOne("select word from word_relation_tmp wr inner join words_tmp w on w.id = wr.word_id where word like binary ?  and product_id = ?  ", array($word, $product));
               $at = 3;
            }
           $this->at = $at;
            return $count == $word? true: $count;
        }
        
        function createDb($encoding)
        {
           
               $vdo = vdo::getVdo(null);
            $vdo->exec("create database " . DB_PREFIX."1; ");
             $vdo->exec("use " . DB_PREFIX."1; ");
            $vdo->exec("CREATE TABLE IF NOT EXISTS `words_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(50) ".$encoding." DEFAULT NULL,
  `word_temp` varchar(50)".$encoding." DEFAULT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `word_relation_tmp` (
  `product_id` int(11) NOT NULL,
  `word_id` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `incategory` int(11) NOT NULL,
  `inname` int(11) NOT NULL,
  `indesc` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`word_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET CHARACTER SET utf8;
            ");
            $vdo = vdo::webshop(1); 
            $vdo->exec("use " . DB_PREFIX."1; ");
              $vdo->exec("SET CHARACTER SET utf8;");
        }
}
    
}