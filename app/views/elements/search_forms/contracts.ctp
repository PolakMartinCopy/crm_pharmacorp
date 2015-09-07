<button id="search_form_show_contract">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['ContractForm']) ){
		$hide = '';
	}
?>
<div id="search_form_contract"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'contracts', 'action' => 'index');
	}?>
	<?php echo $form->create('Contract', array('url' => $url))?>
	<table class="left_heading">
		<tr>
			<th>Název obchodního partnera</th>
			<td><?php echo $form->input('ContractForm.BusinessPartner.name', array('label' => false))?></td>
			<th>IČO</th>
			<td colspan="3"><?php echo $form->input('ContractForm.BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Příjmení odběratele</th>
			<td><?php echo $form->input('ContractForm.Purchaser.last_name', array('label' => false))?></td>
			<th>IČZ</th>
			<td><?php echo $form->input('ContractForm.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td><?php echo $form->input('ContractForm.Purchaser.category', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Dohoda</td>
		</tr>
		<tr>
			<th>Příjmení osoby</th>
			<td><?php echo $this->Form->input('ContractForm.ContactPerson.last_name', array('label' => false, 'type' => 'text'))?></td>
			<th>Datum od</th>
			<td><?php echo $this->Form->input('ContractForm.Contract.date_from', array('label' => false, 'type' => 'text'))?></td>
			<th>Datum do</th>
			<td><?php echo $this->Form->input('ContractForm.Contract.date_to', array('label' => false, 'type' => 'text'))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
				$reset_url = $url;
				$reset_url['reset'] = 'contracts';
				echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>												
	<?php
		echo $form->hidden('ContractForm.Contract.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_contract").click(function () {
		if ($('#search_form_contract').css('display') == "none"){
			$("#search_form_contract").show("slow");
		} else {
			$("#search_form_contract").hide("slow");
		}
	});
	$(function() {
		var model = 'Contract';
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