<h1>Dohody</h1>
<?php
	echo $this->element('search_forms/contracts');

	echo $form->create('CSV', array('url' => array('controller' => 'contracts', 'action' => 'xls_export')));
	echo $form->hidden('data', array('value' => serialize($find)));
	echo $form->hidden('fields', array('value' => serialize($export_fields)));
	echo $form->submit('CSV');
	echo $form->end();
?>

<?php if (empty($contracts)) { ?>
<p><em>V systému nejsou žádné dohody.</em></p>
<?php } else {
	echo $this->element('indexes/contracts');
} ?>