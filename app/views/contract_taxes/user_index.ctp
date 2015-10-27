<h1>Daně dohod</h1>
<ul>
	<li><?php echo $html->link('Přidat daň dohod', array('controller' => 'contract_taxes', 'action' => 'add'))?></li>
</ul>
<?php if (empty($contract_taxes)) { ?>
<p><em>V databázi nejsou žádné daně dohod.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th>ID</th>
		<th>Hodnota</th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($contract_taxes as $contract_tax) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $contract_tax['ContractTax']['id']?></td>
		<td><?php echo $contract_tax['ContractTax']['name']?></td>
		<td class="actions">
			<?php echo $html->link('Upravit', array('controller' => 'contract_taxes', 'action' => 'edit', $contract_tax['ContractTax']['id']))?>
		</td>
	</tr>
<?php } // end foreach?>
</table>
<?php } // end if?>
<ul>
	<li><?php echo $html->link('Přidat daň dohod', array('controller' => 'contract_taxes', 'action' => 'add'))?></li>
</ul>