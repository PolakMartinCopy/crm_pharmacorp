<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('Datum vys.', 'DeliveryNote.date')?></th>
		<th><?php echo $this->Paginator->sort('Číslo dokladu', 'DeliveryNote.code')?></th>
		<th><?php echo $this->Paginator->sort('Odběratel', 'DeliveryNote.purchaser_name')?></th>
		<th><?php echo $this->Paginator->sort('Název zboží', 'Product.name')?></th>
		<th><?php echo $this->Paginator->sort('Mn.', 'ProductsTransaction.quantity')?></th>
		<th><?php echo $this->Paginator->sort('MJ', 'Unit.shortcut')?></th>
		<th><?php echo $this->Paginator->sort('Kč/J', 'ProductsTransaction.unit_price')?></th>
		<th><?php echo $this->Paginator->sort('Celkem', 'DeliveryNote.total_price')?></th>
		<th><?php echo $this->Paginator->sort('VZP kód', 'Product.vzp_code')?></th>
		<th><?php echo $this->Paginator->sort('Kód skupiny', 'Product.group_code')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php 
	$odd = '';
	foreach ($delivery_notes as $transaction) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
	?>
	<tr<?php echo $odd?>>
		<td><?php echo czech_date($transaction['DeliveryNote']['date'])?></td>
		<td><?php echo $this->Html->link($transaction['DeliveryNote']['code'], '/' . DL_FOLDER . $transaction['DeliveryNote']['id'] . '.pdf', array('target' => '_blank')); ?></td>
		<td><?php echo $transaction['DeliveryNote']['purchaser_name']?></td>
		<td><?php echo $transaction['Product']['name']?></td>
		<td><?php echo $transaction['ProductsTransaction']['quantity']?></td>
		<td><?php echo $transaction['Unit']['shortcut']?></td>
		<td><?php echo $transaction['ProductsTransaction']['unit_price']?></td>
		<td><?php echo $transaction['DeliveryNote']['total_price']?></td>
		<td><?php echo $transaction['Product']['vzp_code']?></td>
		<td><?php echo $transaction['Product']['group_code']?></td>
		<td><?php 
			//echo $this->Html->link('Upravit', array('controller' => 'delivery_notes', 'action' => 'edit', $transaction['DeliveryNote']['id'], 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI']))) . ' | ';
			//echo $this->Html->link('Smazat', array('controller' => 'delivery_notes', 'action' => 'delete', $transaction['DeliveryNote']['id'], 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])), array(), 'Opravdu chcete transakci smazat?') . ' | ';
			//echo $this->Html->link('Smazat položku', array('controller' => 'products_transactions', 'action' => 'delete', $transaction['ProductsTransaction']['id'], 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])));
		?></td>
	</tr>
	<?php } ?>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>