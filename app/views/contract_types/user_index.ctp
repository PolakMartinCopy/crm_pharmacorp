<h1>Typy dohod</h1>
<ul>
	<li><?php echo $html->link('Přidat typ dohod', array('controller' => 'contract_types', 'action' => 'add'))?></li>
</ul>
<?php if (empty($contract_types)) { ?>
<p><em>V databázi nejsou žádné typy dohod.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th>ID</th>
		<th>Název</th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($contract_types as $contract_type) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $contract_type['ContractType']['id']?></td>
		<td><?php echo $contract_type['ContractType']['name']?></td>
		<td class="actions">
			<?php echo $html->link('Upravit', array('controller' => 'contract_types', 'action' => 'edit', $contract_type['ContractType']['id']))?>
		</td>
	</tr>
<?php } // end foreach?>
</table>
<?php } // end if?>
<ul>
	<li><?php echo $html->link('Přidat typ dohod', array('controller' => 'contract_types', 'action' => 'add'))?></li>
</ul>