<?php
App::import('Vendor','xtcpdf');

$tcpdf = new XTCPDF();
$textfont = 'dejavusans'; // looks better, finer, and more condensed than 'dejavusans'

$tcpdf->SetAuthor("c.lekarna-obzor.cz");
$tcpdf->SetAutoPageBreak( false );
$tcpdf->setHeaderFont(array($textfont,'',40));
$tcpdf->xheadercolor = array(150,0,0);
$tcpdf->xheadertext = 'PharmaCorp.cz';
$tcpdf->xfootertext = 'Copyright © %d PharmaCorp.cz. All rights reserved.';

// add a page (required with recent versions of tcpdf)
$tcpdf->AddPage();

$tcpdf->SetFillColor(255,255,255);

$tcpdf->SetFont($textfont, 'B', 14);
$tcpdf->Cell(190, 0, 'Dodací list', 0, 0, 'L', false);

// mezera
$tcpdf->Cell(190, 15, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B',11);
$tcpdf->Cell(100, 0, 'Dodavatel', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, 'Odběratel', 0, 1, 'L', false);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'', 8);
$tcpdf->Cell(100, 0, 'Pharmacorp CZ s.r.o.', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, $delivery_note['DeliveryNote']['purchaser_name'], 0, 1, 'L', false);

$street_info = '';
$city_info = '';
if (!empty($delivery_note['Address'])) {
	$street_info = $delivery_note['Address']['street'] . ' ' . $delivery_note['Address']['number'];
	if (!empty($delivery_note['Address']['o_number'])) {
		$street_info .= '/' . $delivery_note['Address']['o_number'];
	}
	$city_info = $delivery_note['Address']['zip'] . ' ' . $delivery_note['Address']['city'];
}
$ico_info = '';

$tcpdf->Cell(100, 0, 'Fillova 260/1', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, $street_info, 0, 1, 'L', false);

$tcpdf->Cell(100, 0, '63800 Brno', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, $city_info, 0, 1, 'L', false);

$tcpdf->Cell(190, 5, "", 0, 1, 'L', false);

$tcpdf->Cell(100, 0, 'IČ: 29372828', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, '', 0, 1, 'L', false);

$tcpdf->Cell(100, 0, 'DIČ: CZ29372828', 0, 0, 'L', false);
$tcpdf->Cell(90, 0, '', 0, 1, 'L', false);

$tcpdf->Cell(190, 15, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'BU',11);
$tcpdf->Cell(190, 0, 'Datum vystavení: ' . czech_date($delivery_note['DeliveryNote']['date']), 0, 1, 'R', false);

$tcpdf->Cell(190, 5, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B',11);
$tcpdf->Cell(20, 0, 'Kód VZP', 0, 0, 'L', false);
$tcpdf->Cell(100, 0, 'Název zboží', 0, 0, 'L', false);
$tcpdf->Cell(20, 0, 'LOT', 0, 0, 'L', false);
$tcpdf->Cell(20, 0, 'EXP', 0, 0, 'L', false);
$tcpdf->Cell(30, 0, 'Množství MJ', 0, 1, 'R', false);

$tcpdf->SetFont($textfont,'', 8);

foreach ($delivery_note['ProductsTransaction'] as $products_transaction) {
	$tcpdf->Cell(20, 0, $products_transaction['Product']['vzp_code'], 0, 0, 'L', false);
	$tcpdf->Cell(100, 0, $products_transaction['product_name'], 0, 0, 'L', false);
	$tcpdf->Cell(20, 0, $products_transaction['lot'], 0, 0, 'L', false);
	$tcpdf->Cell(20, 0, $products_transaction['exp'], 0, 0, 'L', false);
	$quantity_info = $products_transaction['quantity'] . ' ' . $products_transaction['Product']['Unit']['shortcut'];
	$tcpdf->Cell(30, 0, $quantity_info, 0, 1, 'R', false);
}

$tcpdf->Cell(190, 10, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'BU',11);
$tcpdf->Cell(190, 0, 'Aktuální stav skladu (včetně tohoto dodacího listu)', 0, 1, 'L', false);
$tcpdf->SetFont($textfont,'',8);
// seznam zbozi
foreach ($store_items as $store_item) {
	// nactu produktu do leveho sloupce
	$tcpdf->Cell(50, 0, $store_item['Product']['vzp_code'], 0, 0, 'L', false);
	$tcpdf->Cell(100, 0, $store_item['Product']['name'], 0, 0, 'L', false);
	$quantity_info = $store_item['StoreItem']['quantity'] . ' ' . $store_item['Unit']['shortcut'];
	$tcpdf->Cell(40, 0, $quantity_info, 0, 1, 'R', false);
}
$tcpdf->Cell(190, 10, "", 0, 1, 'L', false);

$tcpdf->Cell(190, 0, 'Děkujeme za spolupráci.', 0, 1, 'L', false);

$tcpdf->Cell(100, 0, '', 0, 0, 'L', false);
$user_info = $delivery_note['User']['first_name'] . ' ' . $delivery_note['User']['last_name'];
$tcpdf->Cell(90, 0, 'Vystavil(a): ' . $user_info, 0, 1, 'L', false);

$tcpdf->Cell(190, 15, "", 0, 1, 'L', false);

$tcpdf->Cell(30, 0, 'Příjemce:', 0, 0, 'L', false);
$tcpdf->Cell(70, 0, '.........................', 0, 0, 'L', false);
$tcpdf->Cell(40, 0, 'Razítko a podpis:', 0, 0, 'L', false);
$tcpdf->Cell(50, 0, '.........................', 0, 1, 'L', false);

echo $tcpdf->Output($delivery_note['DeliveryNote']['code'] . '.pdf', 'D');
?>