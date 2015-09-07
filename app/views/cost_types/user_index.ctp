<h1>Typy nákladů</h1>
<ul>
	<li><?php echo $html->link('Přidat typ nákladů', array('controller' => 'cost_types', 'action' => 'add'))?></li>
</ul>
<?php if (empty($cost_types)) { ?>
<p><em>V databázi nejsou žádné typy nákladů.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th>ID</th>
		<th>Název</th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($cost_types as $cost_type) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $cost_type['CostType']['id']?></td>
		<td><?php echo $cost_type['CostType']['name']?></td>
		<td class="actions">
			<?php echo $html->link('Upravit', array('controller' => 'cost_types', 'action' => 'edit', $cost_type['CostType']['id']))?>
		</td>
	</tr>
<?php } // end foreach?>
</table>
<?php } // end if?>
<ul>
	<li><?php echo $html->link('Přidat typ nákladů', array('controller' => 'cost_types', 'action' => 'add'))?></li>
</ul>