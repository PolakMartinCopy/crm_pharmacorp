<h1>Přidat typ dohod</h1>
<ul>
	<li><?php echo $html->link('Zpět na seznam typů dohod', array('controller' => 'contract_types', 'action' => 'index'))?></li>
</ul>

<?php echo $form->create('ContractType', array('url' => array('controller' => 'contract_types', 'action' => 'add')))?>
<table class="left_heading">
	<tr>
		<th>Text</th>
		<td><?php echo $form->input('ContractType.text', array('label' => false, 'cols' => 70, 'rows' => 5))?></td>
	</tr>
</table>
<?php echo $form->submit('Uložit')?>
<?php echo $form->end() ?>

<ul>
	<li><?php echo $html->link('Zpět na seznam typů dohod', array('controller' => 'contract_types', 'action' => 'index'))?></li>
</ul>