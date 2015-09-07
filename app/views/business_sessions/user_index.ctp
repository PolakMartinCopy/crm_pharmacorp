<h1>Obchodní jednání</h1>
<?php
	echo $this->element('search_forms/business_sessions');	
	echo $form->create('CSV', array('url' => array('controller' => 'business_sessions', 'action' => 'xls_export')));
	echo $form->hidden('data', array('value' => serialize($find)));
	echo $form->hidden('fields', array('value' => serialize($export_fields)));
	echo $form->submit('CSV');
	echo $form->end();
	
	if (empty($business_sessions)) {
		$message = 'V databázi nejsou žádná obchodní jednání';
?>
<p><em><?php echo $message?>.</em></p>
<?php } else { 
	echo $this->element('indexes/business_sessions');
} ?>