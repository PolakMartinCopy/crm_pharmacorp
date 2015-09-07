<button id="search_form_show_sale">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['SaleForm']) ){
		$hide = '';
	}
?>
<div id="search_form_sale"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'sales', 'action' => 'index');
	}?>
	<?php echo $form->create('Sale', array('url' => $url))?>
	<table class="left_heading">
		<tr>
			<th>Název obchodního partnera</th>
			<td><?php echo $form->input('SaleForm.BusinessPartner.name', array('label' => false))?></td>
			<th>IČO</th>
			<td colspan="3"><?php echo $form->input('SaleForm.BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Příjmení odběratele</th>
			<td><?php echo $form->input('SaleForm.Purchaser.last_name', array('label' => false))?></td>
			<th>IČZ</th>
			<td><?php echo $form->input('SaleForm.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td><?php echo $form->input('SaleForm.category', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Město</th>
			<td><?php echo $form->input('SaleForm.Address.city', array('label' => false))?></td>
			<th>PSČ</th>
			<td><?php echo $form->input('SaleForm.Address.zip', array('label' => false))?></td>
			<th>Okres</th>
			<td><?php echo $form->input('SaleForm.Address.region', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Poukazy</td>
		</tr>
		<tr>
			<th>Datum od</th>
			<td><?php echo $this->Form->input('SaleForm.Sale.date_from', array('label' => false, 'type' => 'text'))?></td>
			<th>Datum do</th>
			<td><?php echo $this->Form->input('SaleForm.Sale.date_to', array('label' => false, 'type' => 'text'))?></td>
			<th>Obchodník</th>
			<td><?php echo $this->Form->input('SaleForm.Sale.user_id', array('label' => false, 'empty' => true, 'options' => $users))?></td>
		</tr>
		<tr>
			<th>Číslo dokladu</th>
			<td colspan="5"><?php echo $this->Form->input('SaleForm.Sale.code', array('label' => false, 'type' => 'text'))?></td>
		</tr>
		<tr>
			<td colspan="6">Zboží</td>
		</tr>
			<th>Název</th>
			<td><?php echo $this->Form->input('SaleForm.Product.name', array('label' => false))?></td>
			<th>Kód VZP</th>
			<td><?php echo $this->Form->input('SaleForm.Product.vzp_code', array('label' => false))?></td>
			<th>Kód skupiny</th>
			<td><?php echo $this->Form->input('SaleForm.Product.group_code', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
				$reset_url = $url;
				$reset_url['reset'] = 'sales';
				echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>										
	<?php
		echo $form->hidden('SaleForm.Sale.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_sale").click(function () {
		if ($('#search_form_sale').css('display') == "none"){
			$("#search_form_sale").show("slow");
		} else {
			$("#search_form_sale").hide("slow");
		}
	});
	$(function() {
		var model = 'Sale';
		var dateFromId = model + 'Form' + model + 'DateFrom';
		var dateToId = model + 'Form' + model + 'DateTo';
		var dates = $('#' + dateFromId + ',#' + dateToId).datepicker({
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
				var option = this.id == dateFromId ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
	$( "#datepicker" ).datepicker( $.datepicker.regional[ "cs" ] );
</script>