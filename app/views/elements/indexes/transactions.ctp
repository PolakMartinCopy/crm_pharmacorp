<?php
$quantity_field = 'quantity';
$total_price_field = 'total_price'; 
if ($model == 'Sale') {
	$quantity_field = 'abs_quantity';
	$total_price_field = 'abs_total_price';
}
?>

<table class="top_heading">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('Datum vys.', $model . '.date')?></th>
			<th><?php echo $this->Paginator->sort('Číslo dokladu', $model . '.code')?></th>
			<th><?php echo $this->Paginator->sort('Odběratel', $model . '.purchaser_name')?></th>
			<th><?php echo $this->Paginator->sort('Název zboží', 'Product.name')?></th>
			<th><?php echo $this->Paginator->sort('Mn.', $model . '.' . $quantity_field)?></th>
			<th><?php echo $this->Paginator->sort('MJ', 'Unit.shortcut')?></th>
			<th><?php echo $this->Paginator->sort('Kč/J', 'ProductsTransaction.unit_price')?></th>
			<th><?php echo $this->Paginator->sort('Celkem', $model . '.' . $total_price_field)?></th>
			<th><?php echo $this->Paginator->sort('VZP kód', 'Product.vzp_code')?></th>
			<th><?php echo $this->Paginator->sort('Kód skupiny', 'Product.group_code')?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$odd = '';
		foreach ($transactions as $transaction) {
			$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
		?>
		<tr<?php echo $odd?>>
			<td><?php echo czech_date($transaction[$model]['date'])?></td>
			<td><?php
			if ($transaction['TransactionType']['id'] == 1) {
				echo $this->Html->link($transaction[$model]['code'], '/' . DL_FOLDER . $transaction[$model]['code'] . '.pdf', array('target' => '_blank'));
			} else {
				echo $transaction[$model]['code'];
			} ?></td>
			<td><?php
				switch ($model) {
					case 'DeliveryNote': $active_tab = 10; break;
					case 'Sale': $active_tab = 11; break;
					default: $active_tab = 12; break;
				} 
				echo $this->Html->link($transaction[$model]['purchaser_name'], array('controller' => 'purchasers', 'action' => 'view', $transaction['Purchaser']['id'], 'tab' => $active_tab))?></td>
			<td><?php echo $transaction['Product']['name']?></td>
	<?php ?>
			<td align="right"><?php echo $transaction[$model][$quantity_field]?></td>
			<td><?php echo $transaction['Unit']['shortcut']?></td>
			<td align="right"><?php echo $transaction['ProductsTransaction']['unit_price']?></td>
			<td align="right"><?php echo $transaction[$model][$total_price_field]?></td>
			<td><?php echo $transaction['Product']['vzp_code']?></td>
			<td><?php echo $transaction['Product']['group_code']?></td>
			<td><?php 
	//			echo $this->Html->link('Upravit', array('action' => 'edit', $transaction[$model]['id'])) . ' | ';
	//			echo $this->Html->link('Smazat', array('action' => 'delete', $transaction[$model]['id']), array(), 'Opravdu chcete transakci smazat?') . ' | ';
	//			echo $this->Html->link('Smazat položku', array('controller' => 'products_transactions', 'action' => 'delete', $transaction['ProductsTransaction']['id']));
			?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th>Celkem</th>
			<th colspan="3">&nbsp;</th>
			<th align="right"><?php echo abs($transactions_total_quantity)?></th>
			<th colspan="2">&nbsp;</th>
			<th align="right"><?php echo abs($transactions_total_price)?></th>
			<th colspan="3">&nbsp;</th>
		</tr>
	</tfoot>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>