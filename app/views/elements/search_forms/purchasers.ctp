<button id="search_form_show_purchasers">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if (isset($this->data['PurchaserSearch'])) {
		$hide = '';
	}
?>
<div id="search_form_purchasers"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'purchasers', 'action' => 'index');
	}?>
	<?php echo $form->create('Purchaser', array('url' => $url))?>
	<table class="left_heading">
		<tr>
			<th>Název obchodního partnera</th>
			<td><?php echo $form->input('PurchaserSearch.BusinessPartner.name', array('label' => false))?></td>
			<th>IČO</th>
			<td colspan="3"><?php echo $form->input('PurchaserSearch.BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Příjmení odběratele</th>
			<td><?php echo $form->input('PurchaserSearch.Purchaser.last_name', array('label' => false))?></td>
			<th>IČZ</th>
			<td><?php echo $form->input('PurchaserSearch.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td><?php echo $form->input('PurchaserSearch.category', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Město</th>
			<td><?php echo $form->input('PurchaserSearch.Address.city', array('label' => false))?></td>
			<th>PSČ</th>
			<td><?php echo $form->input('PurchaserSearch.Address.zip', array('label' => false))?></td>
			<th>Okres</th>
			<td><?php echo $form->input('PurchaserSearch.Address.region', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">
				<?php
					$reset_url = $url;
					$reset_url['reset'] = 'purchasers';
					echo $html->link('reset filtru', $reset_url);
				?>
			</td>
		</tr>
	</table>
	
	<?php
		echo $form->hidden('PurchaserSearch.Purchaser.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
	
</div>

<script>
	$("#search_form_show_purchasers").click(function () {
		if ($('#search_form_purchasers').css('display') == "none"){
			$("#search_form_purchasers").show("slow");
		} else {
			$("#search_form_purchasers").hide("slow");
		}
	});
</script>