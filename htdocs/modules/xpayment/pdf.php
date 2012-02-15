<?php
/**
 * Invoice Transaction Gateway with Modular Plugin set
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Chronolabs Co-Op http://www.chronolabs.coop/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         xpayment
 * @since           1.30.0
 * @author          Simon Roberts <simon@chronolabs.coop>
 * @translation     Erol Konik <aphex@aphexthemes.com>
 * @translation     Mariane <mariane_antoun@hotmail.com>
 * @translation     Voltan <voltan@icms.ir>
 * @translation     Ezsky <ezskyyoung@gmail.com>
 * @translation     Richardo Costa <lusopoemas@gmail.com>
 * @translation     Kris_fr <kris@fricms.org>
 */

error_reporting(0);
include_once 'header.php';
$GLOBALS['icmsLogger']->activated = false;

	
	
	$invoice_handler =& icms_getModuleHandler('invoice', 'xpayment');
	$invoice_items_handler =& icms_getModuleHandler('invoice_items', 'xpayment');
	
	
	if (isset($_GET['iid'])&&$GLOBALS['xpaymentModuleConfig']['id_protect']==false) {
		$invoice =& $invoice_handler->get($_GET['iid']);
	} elseif (isset($_GET['invoicenum'])&&$GLOBALS['xpaymentModuleConfig']['id_protect']==false) {
		$invoice =& $invoice_handler->getInvoiceNumber($_GET['invoicenum']);
	} else {
		
		$key = $_GET['iid'];
		$criteria = new icms_db_criteria_Item('offline', time(), '>=');
		$criteria->setSort('iid');
		$criteria->setOrder('DESC');
		$count = $invoice_handler->getCount($criteria);
		$invoices = $invoice_handler->getObjects($criteria, true);
		foreach($invoices as $iid => $inv) {
			if ($key==md5($inv->getVar('iid').ICMS_LICENSE_KEY)) {
				$invoice = $inv;
			}
		}
		
	}
	
	if (!is_object($invoice)) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.ICMS_URL.'/modules/xpayment/');
		exit(0);
	}
	
	if (!strpos($invoice->getPDFURL(), $_SERVER['REQUEST_URI'])&&$GLOBALS['xpaymentModuleConfig']['htaccess']==true) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$invoice->getPDFURL());
		exit(0);
	}

	if ($invoice->getVar('offline')<time()) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.ICMS_URL.'/modules/xpayment/');
		exit(0);
	}

	$pdf_data['title'] = sprintf(_XPY_PDF_TITLE, $GLOBALS['icmsConfig']['sitename'], $invoice->getVar('invoicenumber'));
	$pdf_data['subtitle'] = sprintf(_XPY_PDF_SUBTITLE, $invoice->getVar('drawto'), $invoice->getVar('drawfor'), $invoice->getVar('grand'), $invoice->getVar('currency'));
	
	include_once (ICMS_ROOT_PATH. "/class/template.php" );
	$GLOBALS['xoopsTpl'] = new icms_view_Tpl();
	$GLOBALS['xoopsTpl']->assign('invoice', $invoice->getValues());
	$GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['xpaymentModuleConfig']);
	
	if ($invoice->getVar('mode')=='UNPAID')
		$GLOBALS['xoopsTpl']->assign('payment_markup', $invoice->getPaymentHtml());
		
	$criteria = new icms_db_criteria_Item('iid', $invoice->getVar('iid'));
	$items = $invoice_items_handler->getObjects($criteria, true);
	foreach($items as $iiid => $item) {
		$GLOBALS['xoopsTpl']->append('items',  array_merge(array('totals'=>	$item->getTotalsArray(($invoice->getVar('did')!=0))), $item->toArray(($invoice->getVar('did')!=0))));	
	}
	
	ob_start();
	$GLOBALS['xoopsTpl']->display('db:xpayment_payment_pdf.html');
	$pdf_data['content'] = ob_get_contents();
	ob_end_clean();
	
	define ('PDF_CREATOR', $GLOBALS['icmsConfig']['sitename']);
	define ('PDF_AUTHOR', $GLOBALS['icmsConfig']['sitename']);
	define ('PDF_HEADER_TITLE', $pdf_data['title']);
	define ('PDF_HEADER_STRING', $pdf_data['subtitle']);
	define ('PDF_HEADER_LOGO', 'logo.gif');
	define ('K_PATH_IMAGES', ICMS_ROOT_PATH.'/images/');
	
	require_once ICMS_ROOT_PATH.'/libraries/tcpdf/tcpdf.php';
	
	$filename = ICMS_ROOT_PATH.'/libraries/tcpdf/config/lang/'._LANGCODE.'.php';
	if(file_exists($filename)) {
		include_once $filename;
	} else {
		include_once ICMS_ROOT_PATH.'/Frameworks/tcpdf/config/lang/en.php';
	}


	//DNPROSSI Added - xlanguage installed and active 
	$module_handler =& icms::handler('icms_module');
	$xlanguage = $module_handler->getByDirname('xlanguage');
	if ( is_object($xlanguage) && $xlanguage->getVar('isactive') == true ) 
	{ $xlang = true; } else { $xlang = false; }  	
	
	$content = '';
	$content .= $myts->undoHtmlSpecialChars($pdf_data['content']);

	//DNPROSSI Added - Get correct language and remove tags from text to be sent to PDF
	if ( $xlang == true ) { 
	   include_once ICMS_ROOT_PATH.'/modules/xlanguage/include/functions.php';
	   $content = xlanguage_ml($content);
	}

	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$doc_title = $myts->undoHtmlSpecialChars($pdf_data['title']);
	$doc_keywords = 'ICMS';

	//DNPROSSI ADDED gbsn00lp chinese to tcpdf fonts dir
	if (_LANGCODE == "cn") { $pdf->SetFont('gbsn00lp', '', 10); } 

	// set document information
	$pdf->SetCreator($pdf_data['author']);
	$pdf->SetAuthor($pdf_data['author']);
	$pdf->SetTitle($pdf_data['title']);
	$pdf->SetSubject($pdf_data['subtitle']);
	$pdf->SetKeywords($doc_keywords);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
	//$pdf->SetHeaderData('', '', $firstLine, $secondLine);
	//$pdf->SetHeaderData('logo_example.png', '25', $firstLine, $secondLine);
	//UTF-8 char sample
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, '25', 'ֹטיאשלע', $article->title());
	
	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 10, PDF_MARGIN_RIGHT);
	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->setImageScale(1); //set image scale factor
	
	//DNPROSSI ADDED FOR SCHINESE
	if (_LANGCODE == "cn") 
	{ 
		$pdf->setHeaderFont(Array('gbsn00lp', '', 10));
		$pdf->setFooterFont(Array('gbsn00lp', '', 10));
	}
	else 
	{
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	}	
	
	$pdf->setLanguageArray($l); //set language items
	
	//initialize document
	$pdf->AliasNbPages();
	
	// ***** For Testing Purposes
	/*$pdf->AddPage();
	
	// print a line using Cell()
	*$pdf->Cell(0, 10, K_PATH_URL. '  ---- Path Url', 1, 1, 'C');
	$pdf->Cell(0, 10, K_PATH_MAIN. '  ---- Path Main', 1, 1, 'C');
	$pdf->Cell(0, 10, K_PATH_FONTS. '  ---- Path Fonts', 1, 1, 'C');
	$pdf->Cell(0, 10, K_PATH_IMAGES. '  ---- Path Images', 1, 1, 'C');
	*/
	// ***** End Test
	
	$pdf->AddPage();
	$pdf->writeHTML($content, true, 0);
	//Added for buffer error in TCPDF when using chinese charset
	  ob_end_clean();
	$pdf->Output();
	
?>