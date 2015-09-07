<button id="search_form_show_store_item">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['StoreItemForm']) ){
		$hide = '';
	}
?>
<div id="search_form_store_item"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'store_items', 'action' => 'index');
	}?>
	<?php echo $form->create('StoreItem', array('url' => $url)); ?>
	<table class="left_heading">
		<tr>
			<th>Název obchodního partnera</th>
			<td><?php echo $form->input('StoreItemForm.BusinessPartner.name', array('label' => false))?></td>
			<th>IČO</th>
			<td colspan="3"><?php echo $form->input('StoreItemForm.BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Příjmení odběratele</th>
			<td><?php echo $form->input('StoreItemForm.Purchaser.last_name', array('label' => false))?></td>
			<th>IČZ</th>
			<td><?php echo $form->input('StoreItemForm.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td><?php echo $form->input('StoreItemForm.category', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Město</th>
			<td><?php echo $form->input('StoreItemForm.Address.city', array('label' => false))?></td>
			<th>PSČ</th>
			<td><?php echo $form->input('StoreItemForm.Address.zip', array('label' => false))?></td>
			<th>Okres</th>
			<td><?php echo $form->input('StoreItemForm.Address.region', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Zboží</td>
		</tr>
		<tr>
			<th>Název</th>
			<td><?php echo $this->Form->input('StoreItemForm.Product.name', array('label' => false))?></td>
			<th>VZP kód</th>
			<td><?php echo $this->Form->input('StoreItemForm.Product.vzp_code', array('label' => false))?></td>
			<th>Kód skupiny</th>
			<td><?php echo $this->Form->input('StoreItemForm.Product.group_code', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
				$reset_url = $url;
				$reset_url['reset'] = 'store_items';
				echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>
	<?php
		echo $form->hidden('StoreItemForm.StoreItem.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_store_item").click(function () {
		if ($('#search_form_store_item').css('display') == "none"){
			$("#search_form_store_item").show("slow");
		} else {
			$("#search_form_store_item").hide("slow");
		}
	});
</script>