<h1>Přidat daň dohod</h1>
<ul>
	<li><?php echo $html->link('Zpět na seznam daní dohod', array('controller' => 'contract_taxes', 'action' => 'index'))?></li>
</ul>

<?php echo $form->create('ContractTax', array('url' => array('controller' => 'contract_taxes', 'action' => 'add')))?>
<table class="left_heading">
	<tr>
		<th>Hodnota</th>
		<td><?php echo $form->input('ContractTax.name', array('label' => false, 'size' => 5, 'after' => '&nbsp;%'))?></td>
	</tr>
</table>
<?php echo $this->Form->hidden('ContractTax.active', array('value' => true))?>
<?php echo $form->submit('Uložit')?>
<?php echo $form->end() ?>

<ul>
	<li><?php echo $html->link('Zpět na seznam daní dohod', array('controller' => 'contract_taxes', 'action' => 'index'))?></li>
</ul>