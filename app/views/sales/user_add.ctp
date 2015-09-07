<script type="text/javascript">
	$(function() {
		purchaserId = false;

<?php 	if (isset($this->data['Sale']['purchaser_id'])) { ?>
		purchaserId = <?php echo $this->data['Sale']['purchaser_id']?>;
<?php 	} elseif (isset($purchaser['Purchaser']['id'])) { ?>
		purchaserId = <?php echo $purchaser['Purchaser']['id']?>;
<?php 	} ?>

		if (purchaserId) {
			drawStoreItems(purchaserId);
		}

		var rowCount = 1; 
		
		$("#SaleDate").datepicker({
			changeMonth: false,
			numberOfMonths: 1
		});

		$('#SalePurchaserName').autocomplete({
			delay: 500,
			minLength: 2,
			source: '/user/purchasers/autocomplete_list',
			select: function(event, ui) {
				$('#SalePurchaserName').val(ui.item.label);
				$('#SalePurchaserId').val(ui.item.value);
				drawStoreItems(ui.item.value);
				return false;
			}
		});

		$('table').delegate('.ProductsTransactionProductName', 'focusin', function() {
			if ($(this).is(':data(autocomplete)')) return;
			$(this).autocomplete({
				delay: 500,
				minLength: 2,
				source: '/user/products/autocomplete_list',
				select: function(event, ui) {
					var tableRow = $(this).closest('tr');
					var count = tableRow.attr('rel');
					$(this).val(ui.item.label);
					$('#ProductsTransaction' + count + 'ProductId').val(ui.item.value);
					$('#ProductsTransaction' + count + 'Education').val(ui.item.education);
					$('#DeliveryNoteProductsTransaction' + count + 'ProductId').val(ui.item.value);
					return false;
				}
			});
		});
		
		$('table').delegate('.addRowButton', 'click', function(e) {
			e.preventDefault();
			// pridat radek s odpovidajicim indexem na konec tabulky s addRowButton
			var tableRow = $(this).closest('tr');
			tableRow.after(productRow(rowCount));
			// zvysim pocitadlo radku
			rowCount++;
		});

		$('table').delegate('.removeRowButton', 'click', function(e) {
			e.preventDefault();
			var tableRow = $(this).closest('tr');
			tableRow.remove();
		});
	});

	function productRow(count) {
		count++;
		var rowData = '<tr rel="' + count + '">';
		rowData += '<td colspan="2">';
		
		rowData += '<div class="input textarea">';
		rowData += '<textarea name="data[ProductsTransaction][' + count + '][product_name]" class="ProductsTransactionProductName" rows="3" cols="20" id="ProductsTransaction' + count + 'ProductName"></textarea>';
		rowData += '</div>';
		
		rowData += '<input type="hidden" name="data[ProductsTransaction][' + count + '][product_id]" id="ProductsTransaction' + count + 'ProductId" />';
		rowData += '<input type="hidden" name="data[ProductsTransaction][' + count + '][subtract]" id="ProductsTransaction' + count + 'Subtract" value="1" />';
		rowData += '</td>';
		rowData += '<td align="right"><input name="data[ProductsTransaction][' + count + '][quantity]" type="text" size="3" maxlength="10" id="ProductsTransaction' + count + 'Quantity" /></td>';
		rowData += '<td align="right"><input name="data[ProductsTransaction][' + count + '][education]" type="text" size="5" maxlength="10" id="ProductsTransaction' + count + 'Education" /></td>';
<?php if (false) { ?>
		rowData += '<th>Naskladnit</th>';
		rowData += '<td>'
		rowData += '<input type="hidden" name="data[DeliveryNote][ProductsTransaction][' + count + '][product_id]" id="DeliveryNoteProductsTransaction' + count + 'ProductId" />';
		rowData += '<input name="data[DeliveryNote][ProductsTransaction][' + count + '][quantity]" type="text" size="3" id="DeliveryNoteProductsTransaction' + count +'Quantity" />';
		rowData += '</td>';
<?php } ?>
		rowData += '<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>';
		rowData += '</tr>';
		return rowData;
	}

	function drawStoreItems(purchaserId) {
		$.ajax({
			url: '/purchasers/store_items/' + purchaserId,
			dataType: 'json',
			async: false,
			success: function(data) {
				if (data.success) {
//					console.log(data.data);
					fillStoreItems(data.data);
				} else {
					alert(data.message);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			}
		});
	}

	function fillStoreItems(storeItems) {
		var res = '<p><em>Sklad odběratele je prázdný</em></p>.';
		if (storeItems.length > 0) {
			res = '<table class="top_heading">';
			res += '<tr><th>Název</th><th>Množství</th></tr>'
				
			for (i=0; i<storeItems.length; i++) {
				storeItem = storeItems[i];
				res += '<tr><td>' + storeItem.Product.name + '</td><td align="right">' + storeItem.StoreItem.quantity + '</td></tr>';
			}
			res += '</table><br/>';
		}
		$('#StoreItems').html(res);
	}
</script>

<h1>Přidat poukaz</h1>
<ul>
<?php if (isset($purchaser)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam poukazů', $redirect)?></li>
<?php } ?>
</ul>

<div style="width:50%;float:left">
	<?php echo $this->Form->create('Sale', array('url' => $this->passedArgs));?>
	<table class="left_heading" style="width:50%">
		<tr>
			<th style="width:100px">Odběratel</th>
			<td colspan="5"><?php
				if (isset($purchaser)) {
					echo $this->Form->input('Sale.purchaser_name', array('label' => false, 'size' => 30, 'disabled' => true));
				} else {
					echo $this->Form->input('Sale.purchaser_name', array('label' => false, 'size' => 30));
					echo $this->Form->error('Sale.purchaser_id');
				}
				echo $this->Form->hidden('Sale.purchaser_id');
			?></td>
		</tr>
		<tr>
			<th>Popis</th>
			<td colspan="5"><?php echo $this->Form->input('DeliveryNote.description', array('label' => false, 'cols' => 40, 'rows' => 5))?></td>
		</tr>
		<tr>
			<th colspan="2">Zboží</th>
			<th style="text-align:right">Množství</th>
			<th style="text-align:right">Edukace/J</th>
			<th>&nbsp;</th>
		</tr>
		<?php if (empty($this->data['ProductsTransaction'])) { ?>
		<tr rel="0">
			<td colspan="2">
				<?php echo $this->Form->input('ProductsTransaction.0.product_name', array('label' => false, 'class' => 'ProductsTransactionProductName', 'rows' => 3, 'cols' => 20, 'type' => 'textarea'))?>
				<?php echo $this->Form->error('ProductsTransaction.0.product_id')?>
				<?php echo $this->Form->hidden('ProductsTransaction.0.product_id')?>
				<?php echo $this->Form->hidden('ProductsTransaction.0.subtract', array('value' => true))?>
			</td>
			<td align="right"><?php echo $this->Form->input('ProductsTransaction.0.quantity', array('label' => false, 'size' => 3))?></td>
			<td align="right"><?php echo $this->Form->input('ProductsTransaction.0.education', array('label' => false, 'size' => 5))?></td>
	<?php if (false) { ?>
			<th>Naskladnit</th>
			<td>
				<?php echo $this->Form->hidden('DeliveryNote.ProductsTransaction.0.product_id')?>
				<?php echo $this->Form->input('DeliveryNote.ProductsTransaction.0.quantity', array('label' => false, 'size' => 3))?>
			</td>
	<?php } ?>
			<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>
		</tr>
		<?php } else { ?>
		<?php 	foreach ($this->data['ProductsTransaction'] as $index => $data) { ?>
		<tr rel="<?php echo $index?>">
			<td colspan="2">
				<?php echo $this->Form->input('ProductsTransaction.' . $index . '.product_name', array('label' => false, 'class' => 'ProductsTransactionProductName', 'rows' => 3, 'cols' => 20, 'type' => 'textarea'))?>
				<?php echo $this->Form->error('ProductsTransaction.' . $index . '.product_id')?>
				<?php echo $this->Form->hidden('ProductsTransaction.' . $index . '.product_id')?>
				<?php echo $this->Form->hidden('ProductsTransaction.' . $index . '.subtract', array('value' => true))?>
			</td>
			<td align="right"><?php echo $this->Form->input('ProductsTransaction.' . $index . '.quantity', array('label' => false, 'size' => 3))?></td>
			<td align="right"><?php echo $this->Form->input('ProductsTransaction.' . $index . '.education', array('label' => false, 'size' => 5))?></td>
	<?php if (false) { ?>
			<th>Naskladnit</th>
			<td>
				<?php echo $this->Form->hidden('DeliveryNote.ProductsTransaction.' . $index . '.product_id')?>
				<?php echo $this->Form->input('DeliveryNote.ProductsTransaction.' . $index . '.quantity', array('label' => false, 'size' => 3))?>
			</td>
	<?php } ?>
			<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>
		</tr>
		<?php } ?>
		<?php } ?>
	</table>
	<?php echo $this->Form->hidden('Sale.transaction_type_id', array('value' => 3))?>
	<?php echo $this->Form->hidden('DeliveryNote.transaction_type_id', array('value' => 1))?>
	<?php echo $this->Form->hidden('Sale.user_id', array('value' => $user['User']['id']))?>
	<?php echo $this->Form->hidden('DeliveryNote.user_id', array('value' => $user['User']['id']))?>
	<?php echo $this->Form->submit('Uložit')?>
	<?php echo $this->Form->end()?>

	<ul>
	<?php if (isset($purchaser)) { ?>
		<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
	<?php } else { ?>
		<li><?php echo $html->link('Zpět na seznam poukazů', $redirect)?></li>
	<?php } ?>
	</ul>
</div>

<div id="StoreItems" style="width:50%;float:left"></div>
<div style="clear:both"></div>