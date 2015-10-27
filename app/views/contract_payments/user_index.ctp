<h1>Platby dohod</h1>
<ul>
	<li><?php echo $html->link('Přidat platbu dohod', array('controller' => 'contract_payments', 'action' => 'add'))?></li>
</ul>
<?php if (empty($contract_payments)) { ?>
<p><em>V databázi nejsou žádné platby dohod.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th>ID</th>
		<th>Název</th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($contract_payments as $contract_payment) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $contract_payment['ContractPayment']['id']?></td>
		<td><?php echo $contract_payment['ContractPayment']['name']?></td>
		<td class="actions">
			<?php echo $html->link('Upravit', array('controller' => 'contract_payments', 'action' => 'edit', $contract_payment['ContractPayment']['id']))?>
		</td>
	</tr>
<?php } // end foreach?>
</table>
<?php } // end if?>
<ul>
	<li><?php echo $html->link('Přidat platbu dohod', array('controller' => 'contract_payments', 'action' => 'add'))?></li>
</ul>