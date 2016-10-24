<?php
class Invoice extends FPDF {
	
	public $invoice_nr;
	public $date;
	public $company;
	public $address;
	public $purchases;
	
	function createInvoice($invoice_nr,$date,$company,$address,$purchases,$currency,$vat) {
		
		$this->invoice_nr = $invoice_nr;
		$this->date = $date;
		$this->company = utf8_decode($company);
		$this->address = utf8_decode($address);
		$this->purchases = array();
		$this->currency = $currency;
		foreach($purchases as $purchase) {
			array_push($this->purchases,array(utf8_decode($purchase[0]),$purchase[1]));	
		}

		$th = array(__('Pakke'),__('Pris'));
		
		$this->AddPage();
		
		$this->SetFont('Helvetica','',8);

		$this->SetFont('','B');
		$this->MultiCell(50,2.3,$this->company,0,'L');
		$this->Ln(2.3);
		$this->SetFont('');	
		$this->MultiCell(50,2.3,$this->address,0,'L');
		$this->Ln(15);
		$this->createInvoiceInfoTable();
    	$this->Ln(5);
		$this->createDataTable($th,$this->purchases,$vat);
		$this->Ln(20);
		$this->SetFont('','I');
		$this->Cell(0,2.3,utf8_decode(__('Dette beløb vil blive hævet fra din konto i løbet af 5 hverdage.')),NULL,NULL,'C');
		if(!defined('INVOICE_PATH')) define ('INVOICE_PATH','/var/vubla/invoices/' );
		$this->Output(INVOICE_PATH.$this->invoice_nr.'.pdf','F');
	}
	
	function Header() {
		$vbl_address = "Korsgade 40, 2. th.\n9000 Aalborg\nCVR.: 33855044\n+45 29 92 17 11\ninfo@vubla.dk\nwww.vubla.dk";
							
		$logo = 'http://www.vubla.com/assets/img/logos/logo-vubla.png';
		$this->SetFont('Helvetica','',8);
		$this->Cell(10,10,$this->Image($logo,$this->GetX(),$this->GetY(),54.7,17.9));
		$this->Cell(130);
		$this->SetFont('','B');
		$this->MultiCell(50,2.3,'Vubla I/S',0,'L');
		$this->Ln(2.3);
		$this->Cell(140);
		$this->SetFont('');
		$this->MultiCell(50,5,$vbl_address,0,'L');
		$this->Ln(25);
	}
	
	function Footer() {						
		//FooteR?
	}
	
	function createInvoiceInfoTable()
	{
		$th = array(__('Dato'),__('Fakturanr.'));
		$data = array($this->date,$this->invoice_nr);
		
		for($i = 0; $i < count($th); $i++) {
   		// Colors, line width and bold font
    		$this->SetFillColor(63);
    		$this->SetTextColor(255);
    		$this->SetDrawColor(255);
    		$this->SetLineWidth(.6);
    		$this->SetFont('','B');
    		// Header
     	 	$this->Cell(40,6,$th[$i],1,0,'L',true);
    		// Color and font restoration
    		$this->SetFillColor(244,244,244);
    		$this->SetTextColor(0);
    		$this->SetFont('');
    		// Data	
      	$this->Cell(40,6,$data[$i],1,0,'L',1);
      	$this->Ln();
      }
	}
	
	function createDataTable($th,$data,$vat)
	{
   	// Colors, line width and bold font
    	$this->SetFillColor(63,63,63);
    	$this->SetTextColor(255);
    	$this->SetDrawColor(255);
    	$this->SetLineWidth(.6);
    	$this->SetFont('','B');
    	// Header
    	$w = array(150, 40);
    	for($i=0;$i<count($th);$i++)
      	     $this->Cell($w[$i],7,$th[$i],1,0,'C',true);
    	$this->Ln();
    	// Color and font restoration
    	$this->SetFillColor(244);
    	$this->SetTextColor(0);
    	$this->SetFont('');
      $sum = 0;
    	// Data
    	foreach($data as $row)
    	{
    		$sum += $row[1];
    		
        	$this->Cell($w[0],6,$row[0],1,0,'L',1);
        	$this->Cell($w[1],6,vbl_number_format($row[1],2,',','.') .' '.$this->currency,1,0,'R',1);
        	$this->Ln();
    	}
    	
    	$moms = vbl_number_format($vat*$sum/100,2,',','.');
    	
    	$this->SetFillColor(63);
    	$this->SetTextColor(255);
    	$this->Cell(130);
    	$this->SetFont('','B');
    	$this->Cell(20,6,__('Moms ({%vat}%)',array('vat'=>$vat)),1,0,'L',1);
    	$this->SetFillColor(244);
    	$this->SetTextColor(0);
		$this->SetFont('');
    	$this->Cell($w[1],6,$moms . ' '.$this->currency,1,0,'R',1);
    	$this->Ln();
    	$this->SetFillColor(63);
    	$this->SetTextColor(255);
    	$this->Cell(130);
    	$this->SetFont('','B');
    	$this->Cell(20,6,__('Total'),1,0,'L',1);
    	$this->SetFillColor(244);
    	$this->SetTextColor(0);
    	$this->SetFont('');
    	$this->Cell($w[1],6,vbl_number_format($sum+$vat*$sum/100,2,',','.') . ' '.$this->currency,1,0,'R',1);
    	// Closing line
    	$this->Cell(array_sum($w),0,'','T');
	}
}
?>