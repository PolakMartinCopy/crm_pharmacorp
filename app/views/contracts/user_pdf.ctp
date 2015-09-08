<?php
App::import('Vendor','xtcpdf');

$tcpdf = new XTCPDF();
$textfont = 'dejavusans'; // looks better, finer, and more condensed than 'dejavusans'

$tcpdf->SetAuthor(CUST_ROOT);
$tcpdf->SetAutoPageBreak( false );
$tcpdf->setHeaderFont(array($textfont,'',40));
$tcpdf->xheadercolor = array(150,0,0);
$tcpdf->xheadertext = CUST_NAME;
$tcpdf->xfootertext = 'Copyright © %d ' . CUST_NAME . '. All rights reserved.';

// add a page (required with recent versions of tcpdf)
$tcpdf->AddPage();

$tcpdf->SetFillColor(255,255,255);

$tcpdf->SetFont($textfont, 'B', 14);
$tcpdf->Cell(190, 0, 'Dohoda o provedení práce', 0, 0, 'C', false);

// mezera
$tcpdf->Cell(190, 22, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 10);
$tcpdf->Cell(190, 0, 'Zaměstnavatel Pharmacorp CZ s.r.o., Fillova 260/1, 638 00 Brno, IČ: 29372828', 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'', 8);
$tcpdf->Cell(190, 0, 'zastoupený Lukášem Neustupou - jednatelem společnosti', 0, 1, 'L', false);
$tcpdf->Cell(190, 0, 'dále jen Zaměstnavatel', 0, 1, 'L', false);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(190, 0, 'a ' . $contract['ContactPerson']['name'], 0, 1, 'L', false);
$tcpdf->Cell(190, 0, 'nar: ' . db2cal_date($contract['Contract']['birthday']), 0, 1, 'L', false);
$tcpdf->Cell(190, 0, 'bydliště: ' . $contract['Contract']['one_line'], 0, 1, 'L', false);
$tcpdf->Cell(190, 0, 'RČ: ' . $contract['Contract']['birth_certificate_number'], 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'', 8);
$tcpdf->Cell(190, 0, 'dále jen Zaměstnanec', 0, 1, 'L', false);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(190, 0, 'uzavírají', 0, 1, 'C', false);
$tcpdf->SetFont($textfont,'', 8);
$tcpdf->Cell(190, 0, 'v souladu s § 75 a násl. zákona č. 262/2006 Sb. tuto dohodu o provedení práce', 0, 1, 'C', false);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '1)', 0, 0, 'L', false);
$tcpdf->Cell(170, 0, 'Zaměstnanec se zavazuje vykonat pro zaměstnavatele tento pracovní úkol:', 0, 1, 'L', false);
$tcpdf->SetFont($textfont,'', 8);
$tcpdf->Cell(20, 0, '', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, $contract['ContractType']['text'], 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '2)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Rozsah pracovního úkolu maximálně ve výši 25 hodin za daný kalendářní měsíc.', 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '3)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Pracovní úkol bude zahájen dne: ' . $contract['Contract']['begin_date'], 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '4)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Pracovní úkol bude ukončen dne: ' . $contract['Contract']['end_date'], 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '5)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Odměna za provedení pracovního úkolu bude činit maximálně: ' . $contract['Contract']['amount_vat'] . ' Kč vč. DPH (' . $contract['Contract']['amount'] . ' Kč bez DPH)', 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '6)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Odměna je splatná po provedení pracovního úkolu do: ', 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '7)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Odměna bude uhrazena formou bankovního převodu na č. účtu: ' . $contract['Contract']['bank_account'], 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '8)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Zaměstnanec souhlasí, aby zaměstnavatel zpracovával jeho osobní údaje, včetně rodného čísla a uváděl je na pracovněprávních dokumentech.', 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 3, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, '9)', 0, 0, 'L', false);
$tcpdf->MultiCell(170, 0, 'Tato dohoda je sepsána ve dvou vyhotoveních, z nichž jedno obdrží zaměstnanec a jedno zaměstnavatel. Zaměstnanec a zaměstnavatel podepisují tuto dohodu o provedení práce na důkaz souhlasu s jejím obsahem.', 0, 'L', 0, 1);

// mezera
$tcpdf->Cell(190, 15, "", 0, 1, 'L', false);

$tcpdf->SetFont($textfont,'B', 8);
$tcpdf->Cell(20, 0, 'V Brně dne: ', 0, 0, 'L', false);

// mezera
$tcpdf->Cell(190, 15, "", 0, 1, 'L', false);

$tcpdf->Cell(100, 0, '.........................', 0, 0, 'C', false);
$tcpdf->Cell(90, 0, '.........................', 0, 1, 'C', false);
$tcpdf->Cell(100, 0, 'podpis zaměstnance', 0, 0, 'C', false);
$tcpdf->Cell(90, 0, ' podpis (razítko) zaměstnavatele', 0, 1, 'C', false);


echo $tcpdf->Output('dpp_' . $contract['Contract']['id'] . '.pdf', 'D');

?>