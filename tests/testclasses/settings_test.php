<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("SettingsTest");
/**
*
*
*/
class SettingsTest extends BaseDbTest {

    var $globalName;
    var $globalValue;
    var $localName;
    var $localValue;
    var $globalValueForLocal;
    var $globalType;
    var $localType;
    var $globalPossibleValues;
    var $localPossibleValues;
    var $testType;
	var $testPossibleValues;
	var $testValue;
	var $testValue2;
	var $notExistingName;
	var $globalDescription;
	var $globalLongName;

	var $vpdo;
    /*
    * Start the tests
    */
    function setUp() {
        
		$this->wid = 1;
        $this->buildDatabases();
    
        $this->localName = "testNameLocal";
        $this->localValue = "testValueLocal";
		$this->testType = "submit";
	    $this->globalValueForLocal = "testGlobalValueForLocalSetting";
		$this->localType = "Text";
		$this->localPossibleValues = null;
		$this->globalName = "testNameGlobal";
        $this->globalValue = "testValueGlobal";
		$this->globalType = "text";
		$this->globalPossibleValues = null;
		$this->testPossibleValues = array("val1","val2");
		$this->notExistingName = "iDoNotExist";
		$this->testValue = "differentTestValue";
		$this->testValue2 = "yetAnotherTestValue";
		$this->globalDescription = "This is the description of the global setting";
		$this->globalLongName = "Long name of global setting";
        Settings::setGLobal('admin_language',1,$this->wid); 
        $vpdo = VPDO::getVDO(DB_PREFIX.$this->wid);
		$sql = "INSERT INTO settings(name,value) VALUES (".
			$vpdo->quote($this->localName).",".
			$vpdo->quote($this->localValue).")";
		
        $num = $vpdo->exec($sql);
        if($num != 1) {
            exit(1);
        }
        $vpdo = VPDO::getVDO(DB_METADATA);
		$sql = "INSERT INTO settings(name,value,public,possible_values) VALUES (".
			$vpdo->quote($this->globalName).",".
			$vpdo->quote($this->globalValue).",".
			$vpdo->quote(0).",".
			$vpdo->quote($this->globalPossibleValues).")";
		
        $num = $vpdo->exec($sql);
        if($num != 1) {
            exit(1);
        }
        $id = $vpdo->fetchOne('select id from settings where name = ?', array($this->globalName));
        $sql = "INSERT INTO settings_descriptions(lang_id, settings_id, description, long_name) VALUES (".
            $vpdo->quote(1).",".
            $vpdo->quote($id).",".
    
            $vpdo->quote($this->globalDescription).",".
            $vpdo->quote($this->globalLongName).")";
        
        $num = $vpdo->exec($sql);
        if($num != 1) {
            exit(1);
        }
		$sql = "INSERT INTO settings(name,value,public,possible_values) VALUES (".
			$vpdo->quote($this->localName).",".
			$vpdo->quote($this->globalValueForLocal).",".
			$vpdo->quote(1).",".
			$vpdo->quote($this->localPossibleValues).")";
		
        $num = $vpdo->exec($sql);
        if($num != 1) {
            exit(1);
        }
        $this->vpdo = $vpdo;
        $this->buildShopDatabase(3);
    }

    function tearDown()
    {
        $this->wid = 1;
        $this->dropDatabases();
        $this->dropShopDatabase(3);
        $this->vpdo = null;
    }
	
	private function setWid() {
		$_SESSION['wid'] = $this->wid;
	}

	

	function testRelationProperties()
	{
		
		$testValueMaster = "testvalueMaster";
		$testNameMaster = "testNameMaster";
		$this->vpdo->exec("insert into " . DB_PREFIX . 3 . ".settings (`id`, `name`, `value`) values (null, '". $testNameMaster . "', '".$testValueMaster. "')");
	
		$result = Settings::get($testNameMaster, $this->wid);
		$this->assertEquals($testValueMaster, $result, 'The webshop_releations are not working, apperently. ');
		
		$this->vpdo->exec("update  " . DB_PREFIX . 3 . ".settings set skip_inheritance = 1 where name = '". $testNameMaster. "'");
		$result = Settings::get($testNameMaster, $this->wid);
		$this->assertEquals(null, $result, 'The webshop_releations are not working, apparently. This check fails if skip_inheritance i not working. ');

	}

    function testGetMethodToGetGlobalValueNoLocalSettingPressent(){
        $test = Settings::get($this->globalName);
        $this->assertEquals($test , $this->globalValue);
    }

    function testGetMethodToGetLocalValue() {
		$test = Settings::get($this->localName,$this->wid);
		$this->assertEquals($test , $this->localValue);
    }
	
	function testGetMethodToGetNotExistingSetting(){
        $test = Settings::get($this->notExistingName);
        $this->assertNull($test);
    }

    function testGetMethodToGetGlobalWithNoneExistingWid() {
		$test = Settings::get($this->localName,123456789);
		$this->assertEquals($test , $this->globalValueForLocal);
    }

    function testIsPublicForPrivateSetting() {
		$test = Settings::isPublic($this->globalName);
        $this->assertFalse($test);
    }

    function testIsPublicForPublicSetting(){
        $test = Settings::isPublic($this->localName);
        $this->assertTrue($test);
    }

    function testSetTypeAndGetTypeGlobal() {
		$test = Settings::getType($this->globalName);
		$this->assertEquals($test , $this->globalType);
		$test = Settings::setType($this->globalName,$this->testType);
		$this->assertTrue($test);
		$test = Settings::getType($this->globalName);
		$this->assertEquals($test , $this->testType);
    }
	
	function testSetPossibleValuesAndGetPossibleValuesGlobal() {
		$test = Settings::getPossibleValues($this->globalName);
		$this->assertEquals($test , $this->globalPossibleValues);
		$test = Settings::setPossibleValues($this->globalName,$this->testPossibleValues);
		$this->assertTrue($test);
		$test = Settings::getPossibleValues($this->globalName);
		$this->assertEquals($test , $this->testPossibleValues);
    }
	
	function testSetGlobalSettingValueAndGetItAgain() {
		$test = Settings::getGlobal($this->globalName);
		$this->assertEquals($test , $this->globalValue);
		$test = Settings::setGlobal($this->globalName,$this->testValue);
		$this->assertTrue($test);
		$test = Settings::getGlobal($this->globalName);
		$this->assertEquals($test , $this->testValue);
	}
	
	function testSetLocalSettingValueAndGetItAgain() {
		$this->setWid(); //Legacy
		$test = Settings::getLocal($this->localName,$this->wid);
		$this->assertEquals($test , $this->localValue);
		$test = Settings::setLocal($this->localName,$this->testValue,$this->wid);
		$this->assertTrue($test);
		$test = Settings::getLocal($this->localName,$this->wid);
		$this->assertEquals($test , $this->testValue);
	}
	
	function testSetNewLocalSettingValueAndGetItAgain() {
		$this->setWid(); //Legacy
		$test = Settings::getLocal($this->globalName,$this->wid);
		$this->assertNull($test);
		$test = Settings::setLocal($this->globalName,$this->testValue,$this->wid);
		$this->assertTrue($test);
		$test = Settings::getLocal($this->globalName,$this->wid);
		$this->assertEquals($this->testValue,$test);
	}
	
	function testGetLongNameGetGlobalLongPressent(){
        $test = Settings::getLongName($this->globalName);
        $this->assertEquals($test , $this->globalLongName);
    }
	
	function testGetLongNameGetDescriptionPressent(){
        $test = Settings::getDescription($this->globalName);
        $this->assertEquals($test , $this->globalDescription);
    }
	
	function testGetLongNameGetGlobalLongNotPressent(){
        $test = Settings::getLongName($this->notExistingName);
        $this->assertNull($test);
    }
	
	function testGetLongNameGetDescriptionNotPressent(){
        $test = Settings::getLongName($this->notExistingName);
        $this->assertNull($test);
    }
	
	function testSetAllLocal() {
		$test = Settings::getLocal($this->localName,$this->wid);
		$this->assertEquals($test , $this->localValue);
		$test = Settings::getLocal($this->globalName,$this->wid);
		$this->assertNull($test);
		$test = Settings::setAllLocal(
			array(
				$this->localName =>$this->testValue,
				$this->globalName=>$this->testValue2
			),
			$this->wid);
		$this->assertTrue($test);
		$test = Settings::getLocal($this->localName,$this->wid);
		$this->assertEquals($test , $this->testValue);
		$test = Settings::getLocal($this->globalName,$this->wid);
		$this->assertEquals($test , $this->testValue2);
	}
	
	
	function testGetArray() {
		$test = Settings::getSettingsArray(1, 1);
		$this->assertInternalType('array',$test);
		$this->assertCount(8, $test);
		
		$test = Settings::getSettingsArray(1, 1, true);
		$this->assertInternalType('array',$test);
		$this->assertCount(12, $test);
	}
    
    function testSetDescAndLongForAll()
    {
        $_POST =(array) json_decode('{"settings":{"48":{"id":"48","description_1":"Denne indstilling er kun for Magento brugere. Hvis du bruger Magento er det her den n\u00f8gle du skal taste ind n\u00e5r du aktiverer Api brugeren. ","description_2":"EnglishDesc","longname_1":"Magento Api Key","longname_2":"englongname"},"49":{"id":"49","description_1":"Hvis du har problemer med specialtegn for eksempel \u00e6\u00f8\u00e5 kan du vinge denne af og anmod om at blive crawlet igen. Hvis problemet vedbliver kan du kontakte info@vubla.com.","description_2":"","longname_1":"Active encoding","longname_2":""},"52":{"id":"52","description_1":"Hvorvidt Vubla s\u00f8gemaskinen er aktiveret for din webshop.","description_2":"","longname_1":"Aktiveret","longname_2":""},"53":{"id":"53","description_1":"Her kan du specificere en billedsti til dine billeder. Det er praktisk hvis i anvender en assets server eller lignende. ","description_2":"","longname_1":"Billede sti","longname_2":""},"54":{"id":"54","description_1":"Hvis du har problemer med specialtegn, for eksempel \u00e6\u00f8\u00e5, b\u00f8r du \u00e6ndre denne encoding s\u00e5 den stemmer overens med din sides output encoding og anmode om at blive crawlet igen. Hvis problemet vedbliver kan du kontakte info@vubla.com.","description_2":"","longname_1":"Din Encoding","longname_2":""},"55":{"id":"55","description_1":"Hvis din \/vubla.php er beskyttet med Htpasswd. Er du i tvivl efterlad feltet tomt.","description_2":"","longname_1":"HTTP Brugernavn","longname_2":""},"56":{"id":"56","description_1":"Hvis din \/vubla.php er beskyttet med Htpasswd. Er du i tvivl efterlad feltet tomt.","description_2":"","longname_1":"HTTP Password","longname_2":""},"57":{"id":"57","description_1":"Moms angivet som decimal. Hvis dine produkter skal vises inkl. moms, s\u00e5 skal du her indtaste 1.25. Brug . (punktum) i stedet for , (komma).","description_2":"","longname_1":"Moms","longname_2":""},"58":{"id":"58","description_1":"L\u00e6ngde af den produktbeskrivelse der bliver vist. Indtast antal tegn.","description_2":"","longname_1":"Beskrivelses l\u00e6ngde","longname_2":""},"59":{"id":"59","description_1":"Hvis formatet angives til andet end HTML skal du selv bygge eller konfigurere dit modul. ","description_2":"","longname_1":"Resultat format","longname_2":""},"65":{"id":"65","description_1":"Tiden man har til at nulstille sit kodeord i minutter.","description_2":"","longname_1":"Nulstil Password Tid","longname_2":""},"66":{"id":"66","description_1":"Antallet af r\u00e6kker, der normalt vises i s\u00f8ge loggen.","description_2":"","longname_1":"Standard log l\u00e6ngde","longname_2":""},"67":{"id":"67","description_1":"Antallet af r\u00e6kke i mini loggen i statistics.","description_2":"","longname_1":"Standard mini log l\u00e6ngde","longname_2":""},"68":{"id":"68","description_1":"Gr\u00e6nsen for hvorn\u00e5r brugen skal advares om at hans\/hendes pakke er ved at v\u00e6re for lille til webshoppens behov(3% skrives som 1.03).","description_2":"","longname_1":"Pakke advarsels forhold","longname_2":""},"70":{"id":"70","description_1":"Den encoding som vubla serveren benytter","description_2":"","longname_1":"Vubla Encoding","longname_2":""},"71":{"id":"71","description_1":"Antal r\u00e6kke i mest s\u00f8gte","description_2":"","longname_1":"R\u00e6kker s\u00f8gte ord","longname_2":""},"72":{"id":"72","description_1":"Antallet af r\u00e6kker i ikke funde s\u00f8gninger","description_2":"","longname_1":"R\u00e6kker i ikke fundne","longname_2":""},"73":{"id":"73","description_1":"Billedet der vises for produkter, der ikke har et billede tilknyttet.","description_2":"","longname_1":"Standard Billede","longname_2":""},"74":{"id":"74","description_1":"Dette er den relative sti fra hostroot XML output filen(vubla.php). Dette er kun relevant for osComerce og custom webshopsystemer.","description_2":"","longname_1":"Xml Output Fil Placering","longname_2":""},"75":{"id":"75","description_1":"Skal Vubla bruge et standard billede hvis ikke det angivne kan findes","description_2":"","longname_1":"Inds\u00e6t Standard Billede","longname_2":""},"76":{"id":"76","description_1":"Hvis din webshop bruger SmartWeb-shop skal du indtaste din API vubla bruger her.","description_2":"","longname_1":"SmartWeb User","longname_2":""},"77":{"id":"77","description_1":"Hvis din webshop benytter SmartWeb-shop skal du indtaste en vubla API brugers password her.","description_2":"","longname_1":"SmartWeb Password","longname_2":""},"78":{"id":"78","description_1":"URLen til din wsdl file hvis du benytter SmartWeb","description_2":"","longname_1":"SmartWeb WSDL fil","longname_2":""},"79":{"id":"79","description_1":"Session time for normal login","description_2":"","longname_1":"Login Session Time","longname_2":""},"80":{"id":"80","description_1":"Time allowed for admins to be loged in as.","description_2":"","longname_1":"Admin Login Session Time","longname_2":""},"81":{"id":"81","description_1":"","description_2":"","longname_1":"Cookie expire time","longname_2":""},"82":{"id":"82","description_1":"Maksimal l\u00e6ngde af texten til brugerdefinerede n\u00f8gleord","description_2":"","longname_1":"N\u00f8gleords text l\u00e6ngde","longname_2":""},"83":{"id":"83","description_1":"Maksimal n\u00f8gleords l\u00e6ngde","description_2":"","longname_1":"N\u00f8gleord max l\u00e6ngde","longname_2":""},"84":{"id":"84","description_1":"Det maksimale antal s\u00f8geresultater, som Vubla finder ved en s\u00f8gning","description_2":"","longname_1":"Maksimale s\u00f8geresultater","longname_2":""},"85":{"id":"85","description_1":"","description_2":"","longname_1":"Webshop guide osCommerce","longname_2":""},"86":{"id":"86","description_1":"","description_2":"","longname_1":"Webshop guide magento","longname_2":""},"87":{"id":"87","description_1":"","description_2":"","longname_1":"Webshop guide presta","longname_2":""},"88":{"id":"88","description_1":"","description_2":"","longname_1":"Webshop guide custom","longname_2":""},"89":{"id":"89","description_1":"Det minimale antal s\u00f8geresultater, som b\u00f8r vises","description_2":"","longname_1":"Minimale s\u00f8geresultater","longname_2":""},"90":{"id":"90","description_1":"","description_2":"","longname_1":"Mindste lighed","longname_2":""},"91":{"id":"91","description_1":"The address to which support(debugging) mails are send to.","description_2":"","longname_1":"Support e-mail address","longname_2":""},"92":{"id":"92","description_1":"L\u00e6ngde af produktets titel der bliver vist. Indtast antal tegn.","description_2":"","longname_1":"Produkttitel l\u00e6ngde","longname_2":""},"93":{"id":"93","description_1":"Hvis kunden ikke finder noget s\u00e5 vises denne besked. ","description_2":"","longname_1":"Besked ved tom s\u00f8gning","longname_2":""},"94":{"id":"94","description_1":"Ekstra css til produkt titler. Bliver anvendt i style=\"\"","description_2":"","longname_1":"Produkt titel CSS","longname_2":""},"95":{"id":"95","description_1":"Hvis aktiveret vises produktets SKU under ","description_2":"","longname_1":"Vis SKU","longname_2":""},"96":{"id":"96","description_1":"H\u00f8jde af resultaterne","description_2":"","longname_1":"Resultat R\u00e6kkers H\u00f8jde","longname_2":""},"97":{"id":"97","description_1":"Hvis du har flere forretninger p\u00e5 en installation skal du her inds\u00e6tte koden p\u00e5 den s\u00f8gemaskinen er installeret p\u00e5. Hvis alle dine forretninger skal anvendes, inds\u00e6t 0.","description_2":"","longname_1":"Magento Store Code","longname_2":""},"98":{"id":"98","description_1":"Her kan du selv tilf\u00f8je CSS styles til produkter","description_2":"","longname_1":"Ekstra CSS style","longname_2":""},"99":{"id":"99","description_1":"Det \u00f8verste katalog du gerne vil have vist. Bem\u00e6rk: Den \u00f8verste kategori slettes fra listen. ","description_2":"","longname_1":"Magento rod katalog","longname_2":""},"100":{"id":"100","description_1":"\u00c6ndre din style p\u00e5 prisen","description_2":"","longname_1":"Pris CSS Style","longname_2":""},"101":{"id":"101","description_1":"Lav fx overstregning eller r\u00f8d skrift ved den gamle pris vha. CSS","description_2":"","longname_1":"Gamle Pris CSS Style","longname_2":""},"102":{"id":"102","description_1":"Der vises en ramme omkring produktet n\u00e5r kunden holder musen over.","description_2":"","longname_1":"Vis ramme ved hover","longname_2":""},"103":{"id":"103","description_1":"Skjul produkter der ikke er p\u00e5 lager.","description_2":"","longname_1":"Gem ikke p\u00e5 lager produkter","longname_2":""},"104":{"id":"104","description_1":"Her kan der tilf\u00f8jes CSS style til produktnavnet, som vises n\u00e5r kunden holder musen over det.","description_2":"","longname_1":"CSS style til produkt navn ved hover","longname_2":""},"105":{"id":"105","description_1":"","description_2":"","longname_1":"Vubla info mail addresse","longname_2":""},"106":{"id":"106","description_1":"K\u00f8b nu kanp returnerer s\u00f8geren til resultat siden efter at have lagt produkter i kurven. Virker pt. kun p\u00e5 bgsys og oscommerce. ","description_2":"","longname_1":"K\u00f8b nu knap returner til resultater","longname_2":""},"107":{"id":"107","description_1":"Vis produktets model under titlen.","description_2":"","longname_1":"Vis produktets model","longname_2":""},"108":{"id":"108","description_1":"V\u00e6lg den m\u00f8ntfod du vil have til at st\u00e5r efter dine produkters pris.","description_2":"","longname_1":"Standard m\u00f8ntfod","longname_2":""},"109":{"id":"109","description_1":"Her skrives h\u00f8jden af billederne, som vises i vublas s\u00f8ge resultater. H\u00f8jden angives i pixels. 100 er standarden.","description_2":"","longname_1":"Billede h\u00f8jde","longname_2":""},"110":{"id":"110","description_1":"Skal widgets (kategorier og pris slider) nulstilles n\u00e5r der laves en ny s\u00f8gning.","description_2":"","longname_1":"Nulstil Widgets ved S\u00f8gning","longname_2":""},"111":{"id":"111","description_1":"Skal v\u00e6rkt\u00f8jslinjen med sortering og \"vis alle\" nulstilles ved en ny s\u00f8gning.","description_2":"","longname_1":"Nulstil v\u00e6rkt\u00f8jslinje ved s\u00f8gning","longname_2":""},"112":{"id":"112","description_1":"","description_2":"","longname_1":"magento_buy_now_return_to_search","longname_2":""},"115":{"id":"115","description_1":"Bredden i procent af venstre side.","description_2":"","longname_1":"Venstre menu bredde","longname_2":""},"114":{"id":"114","description_1":"Vis produktets lager status. Brug | som seperator. F\u00f8rste del vises hvis det er p\u00e5 lager.","description_2":"","longname_1":"Vis lager status","longname_2":""},"116":{"id":"116","description_1":"Antal forslag der vises n\u00e5r en kunde starter med at skrive en s\u00f8gning.","description_2":"","longname_1":"Antal Forslag","longname_2":""},"117":{"id":"117","description_1":"H\u00f8jden af forslag der vises til kunden n\u00e5r der startes med at blive indtastet noget i s\u00f8gefeltet.","description_2":"","longname_1":"Forslag H\u00f8jde","longname_2":""},"118":{"id":"118","description_1":"Bredden p\u00e5 forslag, som vises til kunden n\u00e5r en s\u00f8gning indtastes","description_2":"","longname_1":"Forslag Bredde","longname_2":""},"119":{"id":"119","description_1":"Set til \"on\" hvis priser gemmes med moms. ","description_2":"","longname_1":"Priser gemmes med moms","longname_2":""},"120":{"id":"120","description_1":"Her skrives den m\u00e5de s\u00f8gefelter skal identificeres p\u00e5 i en komma separeret liste. Har dine s\u00f8gefelte eksempelvis alle sammen id=\"search\", skal du skrive #search is dette felt.","description_2":"","longname_1":"ID til s\u00f8gefelter","longname_2":""},"121":{"id":"121","description_1":"Indtast identifikation p\u00e5 de s\u00f8geforme der kan optr\u00e6de p\u00e5 s\u00f8geside. Disse indtastes kommasepareret i CSS form, fx hvis formen med id \"search\" kan optr\u00e6de p\u00e5 s\u00f8geside, skal der skrives \"#search\" is dette felt.","description_2":"","longname_1":"S\u00f8ge formes ID","longname_2":""},"122":{"id":"122","description_1":"Skal der bruges autocomplete p\u00e5 s\u00f8gninger p\u00e5 din webshop.","description_2":"","longname_1":"Autocomplete","longname_2":""},"123":{"id":"123","description_1":"Det store id der ikke indeholder moms. Ex. dk_erhverv.","description_2":"","longname_1":"Magento store id uden moms","longname_2":""},"124":{"id":"124","description_1":"Indeholder en beskrivelse af hvad der skal vises under prisen. Syntax er: \"besked-med-moms|besked_uden_moms\".\r\nEks.Eksl. Moms<\/div>|Inkl. Moms<\/div>","description_2":"","longname_1":"Moms besked","longname_2":""},"125":{"id":"125","description_1":"Ved sidens load fokuseres der automatisk p\u00e5 s\u00f8gefeltet","description_2":"","longname_1":"Fokuser ved load","longname_2":""},"126":{"id":"126","description_1":"","description_2":"","longname_1":"gwo._setAccount","longname_2":""},"127":{"id":"127","description_1":"","description_2":"","longname_1":"gwo._trackPageview","longname_2":""},"128":{"id":"128","description_1":"","description_2":"","longname_1":"gwo.k","longname_2":""},"129":{"id":"129","description_1":"Some Magentos ignore this for some reason. ","description_2":"","longname_1":"Ignore Magento Discount Price Expire Date","longname_2":""},"130":{"id":"130","description_1":"H\u00f8jden af topbaren.","description_2":"","longname_1":"Topbarens h\u00f8jde","longname_2":""},"131":{"id":"131","description_1":"Kryds af hvis vublas billed cache skal bruges til at give hurtigere adgang til de billeder der vises i s\u00f8geresultater.","description_2":"","longname_1":"Brug Billed-cache","longname_2":""},"132":{"id":"132","description_1":"Det sprog du \u00f8nsker p\u00e5 siden. ","description_2":"","longname_1":"Administrations Sprog","longname_2":""},"133":{"id":"133","description_1":"Skal vi k\u00f8re split test p\u00e5 din webshop","description_2":"","longname_1":"Split Test","longname_2":""},"134":{"id":"134","description_1":"","description_2":"","longname_1":"Brug API Output","longname_2":""}},"controller":"settings","action":"language","submitSettingsLang":"Gem"}',true);
        Settings::setDescAndLongForAll($_POST);
        Settings::setGLobal('admin_language',2,$this->wid); // Set it to english
        Settings::setLocal('admin_language',2,$this->wid); // Set it to engli
        Language::reset();
        $this->assertEquals(2,Language::get()->getId());
        $exp = Settings::getDescription('mage_api_key');
        $this->assertEquals('EnglishDesc', $exp);
        $exp = Settings::getLongName('mage_api_key');
        $this->assertEquals('englongname', $exp);
    }

}

?>
