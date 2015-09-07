<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('Datum vys.', 'Sale.date')?></th>
		<th><?php echo $this->Paginator->sort('Číslo dokladu', 'Sale.code')?></th>
		<th><?php echo $this->Paginator->sort('Odběratel', 'Purchaser.name')?></th>
		<th><?php echo $this->Paginator->sort('Název zboží', 'Product.name')?></th>
		<th><?php echo $this->Paginator->sort('Mn.', 'Sale.abs_quantity')?></th>
		<th><?php echo $this->Paginator->sort('MJ', 'Unit.shortcut')?></th>
		<th><?php echo $this->Paginator->sort('Kč/J', 'ProductsTransaction.unit_price')?></th>
		<th><?php echo $this->Paginator->sort('Marže produktu', 'ProductsTransaction.product_margin')?></th>
		<th><?php echo $this->Paginator->sort('Celkem', 'Sale.abs_total_price')?></th>
		<th><?php echo $this->Paginator->sort('Marže', 'Sale.abs_margin')?></th>
		<th><?php echo $this->Paginator->sort('VZP kód', 'Product.vzp_code')?></th>
		<th><?php echo $this->Paginator->sort('Kód skupiny', 'Product.group_code')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php
	$odd = '';
	foreach ($sales as $transaction) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
	?>
	<tr<?php echo $odd?>>
		<td><?php echo czech_date($transaction['Sale']['date'])?></td>
		<td><?php echo $transaction['Sale']['code']?></td>
		<td><?php echo $transaction['Purchaser']['name']?></td>
		<td><?php echo $transaction['Product']['name']?></td>
		<td><?php echo $transaction['Sale']['abs_quantity']?></td>
		<td><?php echo $transaction['Unit']['shortcut']?></td>
		<td><?php echo $transaction['ProductsTransaction']['unit_price']?></td>
		<td><?php echo $transaction['ProductsTransaction']['product_margin']?></td>
		<td><?php echo $transaction['Sale']['abs_total_price']?></td>
		<td><?php echo $transaction['Sale']['abs_margin']?></td>
		<td><?php echo $transaction['Product']['vzp_code']?></td>
		<td><?php echo $transaction['Product']['group_code']?></td>
		<td><?php
			// echo $this->Html->link('Upravit', array('controller' => 'sales', 'action' => 'edit', $transaction['Sale']['id'], 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI']))) . ' | ';
			// echo $this->Html->link('Smazat', array('controller' => 'sales', 'action' => 'delete', $transaction['Sale']['id'], 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])), array(), 'Opravdu chcete transakci smazat?') . ' | ';
			// echo $this->Html->link('Smazat položku', array('controller' => 'products_transactions', 'action' => 'delete', $transaction['ProductsTransaction']['id'], 'purchaser_id' => $business_partner['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI']));
		?></td>
	</tr>
	<?php } ?>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>