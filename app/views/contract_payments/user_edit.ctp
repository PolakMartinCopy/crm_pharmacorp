<h1>Upravit platbu dohod</h1>
<ul>
	<li><?php echo $html->link('Zpět na seznam plateb dohod', array('controller' => 'contract_payments', 'action' => 'index'))?></li>
</ul>

<?php echo $form->create('ContractPayment', array('url' => array('controller' => 'contract_payments', 'action' => 'add')))?>
<table class="left_heading">
	<tr>
		<th>Název</th>
		<td><?php echo $form->input('ContractPayment.name', array('label' => false))?></td>
	</tr>
</table>
<?php echo $form->hidden('ContractPayment.id')?>
<?php echo $form->submit('Uložit')?>
<?php echo $form->end() ?>

<ul>
	<li><?php echo $html->link('Zpět na seznam plateb dohod', array('controller' => 'contract_payments', 'action' => 'index'))?></li>
</ul>