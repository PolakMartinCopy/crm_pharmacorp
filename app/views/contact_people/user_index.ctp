<h1>Kontaktní osoby</h1>
<?php
echo $this->element('search_forms/contact_people');
echo $form->create('CSV', array('url' => array('controller' => 'contact_people', 'action' => 'xls_export')));
echo $form->hidden('data', array('value' => serialize($find)));
echo $form->hidden('fields', array('value' => serialize($export_fields)));
echo $form->submit('CSV');
echo $form->end();

if (empty($contact_people)) {
?>
<p><em>V databázi nejsou žádné kontaktní osoby.</em></p>
<?php } else { 
	echo $this->element('indexes/contact_people');
} // end if?>