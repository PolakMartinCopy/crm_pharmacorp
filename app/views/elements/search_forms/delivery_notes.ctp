<button id="search_form_show_delivery_note">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['DeliveryNoteForm']) ){
		$hide = '';
	}
?>
<div id="search_form_delivery_note"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'delivery_notes', 'action' => 'index');
	}?>
	<?php echo $form->create('DeliveryNote', array('url' => $url))?>
	<table class="left_heading">
		<tr>
			<th>Název obchodního partnera</th>
			<td><?php echo $form->input('DeliveryNoteForm.BusinessPartner.name', array('label' => false))?></td>
			<th>IČO</th>
			<td colspan="3"><?php echo $form->input('DeliveryNoteForm.BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Příjmení odběratele</th>
			<td><?php echo $form->input('DeliveryNoteForm.Purchaser.last_name', array('label' => false))?></td>
			<th>IČZ</th>
			<td><?php echo $form->input('DeliveryNoteForm.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td><?php echo $form->input('DeliveryNoteForm.category', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Město</th>
			<td><?php echo $form->input('DeliveryNoteForm.Address.city', array('label' => false))?></td>
			<th>PSČ</th>
			<td><?php echo $form->input('DeliveryNoteForm.Address.zip', array('label' => false))?></td>
			<th>Okres</th>
			<td><?php echo $form->input('DeliveryNoteForm.Address.region', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Dodací listy</td>
		</tr>
		<tr>
			<th>Datum od</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.DeliveryNote.date_from', array('label' => false))?></td>
			<th>Datum do</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.DeliveryNote.date_to', array('label' => false))?></td>
			<th>Obchodník</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.DeliveryNote.user_id', array('label' => false, 'empty' => true, 'options' => $users))?></td>
		</tr>
		<tr>
			<th>Číslo dokladu</th>
			<td colspan="5"><?php echo $this->Form->input('DeliveryNoteForm.DeliveryNote.code', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Zboží</td>
		</tr>
			<th>Název</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.Product.name', array('label' => false))?></td>
			<th>Kód VZP</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.Product.vzp_code', array('label' => false))?></td>
			<th>Kód skupiny</th>
			<td><?php echo $this->Form->input('DeliveryNoteForm.Product.group_code', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
				$reset_url = $url;
				$reset_url['reset'] = 'delivery_notes';
				echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>											
	<?php
		echo $form->hidden('DeliveryNoteForm.DeliveryNote.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_delivery_note").click(function () {
		if ($('#search_form_delivery_note').css('display') == "none"){
			$("#search_form_delivery_note").show("slow");
		} else {
			$("#search_form_delivery_note").hide("slow");
		}
	});
	$(function() {
		var model = 'DeliveryNote';
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