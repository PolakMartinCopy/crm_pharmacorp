<h1>Sklady odběratelů</h1>
<?php
	echo $this->element('search_forms/stores');

	echo $form->create('CSV', array('url' => array('controller' => 'store_items', 'action' => 'xls_export')));
	echo $form->hidden('data', array('value' => serialize($find)));
	echo $form->hidden('fields', array('value' => serialize($export_fields)));
	echo $form->submit('CSV');
	echo $form->end();
?>

<?php if (empty($stores)) { ?>
<p><em>Sklady všech odběratelů jsou prázdné</em></p>
<?php } else {
	echo $this->element('indexes/store_items');
} ?>