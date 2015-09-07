<table class="top_heading">
	<thead>
		<tr>
			<th><?php echo $paginator->sort('ID', 'StoreItem.id')?></th>
			<th><?php echo $paginator->sort('Kód VZP', 'Product.vzp_code')?></th>
			<th><?php echo $paginator->sort('Název zboží', 'Product.name')?></th>
			<th><?php echo $paginator->sort('Mn.', 'StoreItem.quantity')?></th>
			<th><?php echo $paginator->sort('MJ', 'Unit.shortcut')?></th>
			<th><?php echo $paginator->sort('Kč/J', 'Product.price')?></th>
			<th><?php echo $paginator->sort('Kč', 'StoreItem.item_total_price')?></th>
			<th><?php echo $paginator->sort('Kód skupiny', 'Product.group_code')?></th>
			<th>Posl. poukaz</th>
		</tr>
	</thead>
	<tbody>
<?php
	$odd = '';
	foreach ($store_items as $store_item) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
		<tr<?php echo $odd?>>
			<td><?php echo $store_item['StoreItem']['id']?></td>
			<td><?php echo $store_item['Product']['vzp_code']?></td>
			<td><?php echo $store_item['Product']['name']?></td>
			<td><?php echo $store_item['StoreItem']['quantity']?></td>
			<td><?php echo $store_item['Unit']['shortcut']?></td>
			<td><?php echo $store_item['Product']['price']?></td>
			<td><?php echo $store_item['StoreItem']['item_total_price']?></td>
			<td><?php echo $store_item['Product']['group_code']?></td>
			<td><?php echo czech_date($store_item['StoreItem']['last_sale_date'])?></td>
		</tr>
	</tbody>
<?php } ?>
	<tfoot>
		<tr>
			<th>Celkem</th>
			<th colspan="2">&nbsp;</th>
			<th><?php echo $store_items_quantity?></th>
			<th colspan="2">&nbsp;</th>
			<th><?php echo $store_items_price?></td>
			<th colspan="2">&nbsp;</th>
		</tr>
	</tfoot>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>