<h1><?php echo ucfirst($header)?></h1>
<?php
	$element = 'transactions';
	if ($model == 'DeliveryNote') {
		$element = 'delivery_notes';
	} elseif ($model == 'Sale') {
		$element = 'sales';
	}
	echo $this->element('search_forms/' . $element);

	echo $form->create('CSV', array('url' => array('controller' => $this->params['controller'], 'action' => 'xls_export')));
	echo $form->hidden('data', array('value' => serialize($find)));
	echo $form->hidden('fields', array('value' => serialize($export_fields)));
	echo $form->submit('CSV');
	echo $form->end();
?>

<?php if (empty($transactions)) { ?>
<p><em>V systému nejsou žádné <?php echo $header?>.</em></p>
<?php } else {
	echo $this->element('indexes/transactions');
} ?>