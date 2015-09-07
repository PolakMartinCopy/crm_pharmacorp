<h1>Typy odběratelů</h1>
<ul>
	<li><?php echo $this->Html->link('Přidat typ odběratele', array('controller' => 'purchaser_types', 'action' => 'add'))?></li>
</ul>

<?php if (empty($purchaser_types)) { ?>
<p><em>V systému nejsou žádné typy odběratelů.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('ID', 'PurchaserType.id')?></th>
		<th><?php echo $this->Paginator->sort('Název', 'PurchaserType.name')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php foreach ($purchaser_types as $purchaser_type) { ?>
	<tr>	
		<td><?php echo $purchaser_type['PurchaserType']['id']?></td>
		<td><?php echo $purchaser_type['PurchaserType']['name']?></td>
		<td><?php
			echo $this->Html->link('Upravit', array('action' => 'edit', $purchaser_type['PurchaserType']['id'])) . ' | ';
			echo $this->Html->link('Smazat', array('action' => 'delete', $purchaser_type['PurchaserType']['id']), array(), 'Opravdu chcete typ odběratele ' . $purchaser_type['PurchaserType']['name'] . ' odstranit?');
		?></td>
	</tr>
	<?php } ?>
</table>
<?php } ?>

<ul>
	<li><?php echo $this->Html->link('Přidat typ odběratele', array('controller' => 'purchaser_types', 'action' => 'add'))?></li>
</ul>