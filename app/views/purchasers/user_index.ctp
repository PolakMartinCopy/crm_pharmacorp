<h1>Odběratelé</h1>
<?php
	echo $this->element('search_forms/purchasers');

	echo $form->create('CSV', array('url' => array('controller' => 'purchasers', 'action' => 'xls_export')));
	echo $form->hidden('data', array('value' => serialize($find)));
	echo $form->hidden('fields', array('value' => serialize($export_fields)));
	echo $form->submit('CSV');
	echo $form->end();
?>

<ul>
	<li><?php echo $html->link('Přidat odběratele', array('controller' => 'purchasers', 'action' => 'add') + $this->passedArgs)?></li>
</ul>

<?php if (empty($purchasers)) {
	$message = 'V databázi nejsou žádní odběratelé';
	if (isset($reset)) {
		$message .= ' odpovídající zadaným parametrům';
	}
?>
<p><em><?php echo $message?>.</em></p>
<?php } else {
	if (isset($this->data['Purchaser'])) {
		$paginator->options(array('url' => $this->data['Purchaser']));
	}

	echo $this->element('indexes/purchasers', array('purchasers' => $purchasers));

	echo $paginator->numbers();
	echo $paginator->prev('« Předchozí ', null, null, array('class' => 'disabled'));
	echo $paginator->next(' Další »', null, null, array('class' => 'disabled'));
?>

<?php } // end if?>
<ul>
	<li><?php echo $html->link('Přidat odběratele', array('controller' => 'purchasers', 'action' => 'add') + $this->passedArgs)?></li>
</ul>