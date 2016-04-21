<script>
	$(document).ready(function(){
		var purchaserId = false;
		
		$('.education').hide();
<?php if (isset($this->data['BusinessSession']['is_education']) && $this->data['BusinessSession']['is_education']) { ?>
		$('.education').show();
<?php } ?>

		$('#BusinessSessionIsEducation').change(function() {
			if ($(this).is(':checked')) {
				$('.education').show();
			} else {
				$('.education').hide();
			}
		});
		
		$('#BusinessSessionPurchaserName').autocomplete({
			delay: 500,
			minLength: 2,
			source: '/user/purchasers/autocomplete_list',
			select: function(event, ui) {
				purchaserId = ui.item.value;
				$('#BusinessSessionPurchaserName').val(ui.item.label);
				$('#BusinessSessionPurchaserId').val(ui.item.value);
				return false;
			}
		});

		$('table').delegate('.BusinessSessionsCostName', 'focusin', function() {
			if ($(this).is(':data(autocomplete)')) return;
			$(this).autocomplete({
				state: 'notSelected',
				delay: 500,
				minLength: 2,
				source: '/user/business_session_cost_items/autocomplete_list',
				select: function(event, ui) {
					var tableRow = $(this).closest('tr');
					var count = tableRow.attr('data-cost-count');
					$(this).val(ui.item.label);
					$('#BusinessSessionsCost' + count + 'BusinessSessionCostItemId').val(ui.item.value);
					$('#BusinessSessionsCost' + count + 'Price').val(ui.item.price);
					$(this).autocomplete('option', {state: 'selected'});
					return false;
				},
				change: function(event, ui) {
					if ($(this).autocomplete('option', 'state') != 'selected') {
						var tableRow = $(this).closest('tr');
						var count = tableRow.attr('data-cost-count');
						$('#BusinessSessionsCost' + count + 'BusinessSessionCostItemId').val(null);
					}
				}
			});
		});
		
		var maximum = 0;
<?php if (isset($this->data['BusinessSessionsCost']) && !empty($this->data['BusinessSessionsCost'])) { ?>
		$('.cost').each(function() {
			var value = parseFloat($(this).attr('data-cost-count'));
			maximum = (value > maximum) ? value : maximum;
		});
<?php } ?>
				var rowCount = maximum + 1;

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

		var maximum = 0;
<?php if (isset($this->data['Contract']) && !empty($this->data['Contract'])) { ?>
		$('.education').each(function() {
			var value = parseFloat($(this).attr('data-education-count'));
			maximum = (value > maximum) ? value : maximum;
		});
<?php } ?>
		var educationRowCount = maximum + 1;

		$('table').delegate('.addEducationRowButton', 'click', function(e) {
			e.preventDefault();
			// zjistim cislo edukace, za kterou chci pridat dalsi
			var rowEducationCount = $(this).closest('tr').attr('data-education-count');
			$('tr[data-education-count=' + rowEducationCount + ']').last().after(educationRow(educationRowCount));
			// zvysim pocitadlo radku
			educationRowCount++;
		});

		$('table').delegate('.removeEducationRowButton', 'click', function(e) {
			e.preventDefault();
			// zjistim cislo edukace, kterou chci odstranit
			var rowEducationCount = $(this).closest('tr').attr('data-education-count');
			// smazu vsechny radky tabulky, ktere maji data-education-count roven danemu cislu edukace
			$('tr[data-education-count=' + rowEducationCount + ']').remove();
		});

		$('table').delegate('.contact-person-name', 'focusin', function() {
			if ($(this).is(':data(autocomplete)')) return;
			contactPeopleAutocompleteURL = '/user/contact_people/autocomplete_list';
			if (purchaserId) {
				contactPeopleAutocompleteURL += '/0/' + purchaserId;
			}
			
			$(this).autocomplete({
				delay: 500,
				minLength: 2,
				source: contactPeopleAutocompleteURL,
				select: function(event, ui) {
					count = $(this).closest('tr').attr('data-education-count');
					var fieldName = '#Contract' + count + 'ContactPersonName';
					var fieldId = '#Contract' + count + 'ContactPersonId';
					var fieldBankAccount = '#Contract' + count + 'BankAccount';
					$(fieldName).val(ui.item.label);
					$(fieldId).val(ui.item.value);
					$(fieldBankAccount).val(ui.item.bank_account);
					return false;
				}
			});
		});

		$('table').delegate('.contract-date', 'focusin', function() {
			count = $(this).closest('tr').attr('data-education-count');
			var dateFromId = 'Contract' + count + 'BeginDate';
			var dateToId = 'Contract' + count + 'EndDate';
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
			$("#datepicker").datepicker($.datepicker.regional[ "cs" ]);
		});

		var dates = $( "#BusinessSessionDate" ).datepicker({
			defaultDate: "+1w",
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
				var option = this.id == "BusinessSessionDate" ? "minDate" : "maxDate",
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

function productRow(count) {
	count++;
	var rowData = '<tr class="cost" data-cost-count="' + count + '">';
	rowData += '<td colspan="2">';
	rowData += '<input name="data[BusinessSessionsCost][' + count + '][name]" type="text" class="BusinessSessionsCostName" size="30" id="BusinessSessionsCost' + count + 'Name" />';
	rowData += '<input type="hidden" name="data[BusinessSessionsCost][' + count + '][business_session_cost_item_id]" id="BusinessSessionsCost' + count + 'BusinessSessionCostItemId">';
	rowData += '</td>';
	rowData += '<td><select name="data[BusinessSessionsCost][' + count + '][cost_type_id]" id="BusinessSessionsCost' + count + 'CostTypeId">';
	<?php foreach ($cost_types as $index => $name) { ?>
	rowData += '<option value="<?php echo $index?>"><?php echo $name ?></option>';
	<?php } ?>
	rowData += '</select></td>';
	rowData += '<td align="right"><input name="data[BusinessSessionsCost][' + count + '][quantity]" type="text" size="3" maxlength="10" id="BusinessSessionsCost' + count + 'Quantity" />';
	rowData += '<td align="right"><input name="data[BusinessSessionsCost][' + count + '][price]" type="text" size="5" maxlength="20" id="BusinessSessionsCost' + count + 'Price" />';
	rowData += '</td>';
	rowData += '<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>';
	rowData += '</tr>';
	return rowData;
}

function educationRow(count) {
	var rowData = '<tr class="education" data-education-count="' + count + '"><td colspan="6">' + (count + 1) + '. edukace</td></tr>';
	rowData += '<tr class="education" data-education-count="' + count + '">';
	rowData += '<th style="width:100px">Kontaktní osoba</th>';
	rowData += '<td colspan="4">';
	rowData += '<input name="data[Contract][' + count + '][contact_person_name]" type="text" size="50" id="Contract' + count + 'ContactPersonName" class="contact-person-name">';
	rowData += '<input type="hidden" name="data[Contract][' + count + '][contact_person_id]" id="Contract' + count + 'ContactPersonId">';
	rowData += '</td>';
	rowData += '<td rowspan="6"><a href="#" class="removeEducationRowButton">-</a></td>';
	rowData += '</tr>';
	rowData += '<tr class="education" data-education-count="' + count + '">';
	rowData += '<th>Typ dohody</th>';
	rowData += '<td colspan="4"><select name="data[Contract][' + count + '][contract_type_id]" id="Contract' + count + 'ContractTypeId">';
<?php foreach ($contract_types as $index => $value) { ?>
	rowData += '<option value="<?php echo $index?>"><?php echo $value?></option>';
<?php } ?>
	rowData += '</select></td>';
	rowData += '</tr>';
	rowData += '<tr class="education" data-education-count="' + count + '">';
	rowData += '<th>Číslo bank. účtu</th>';
	rowData += '<td colspan="4"><div class="input text required"><input name="data[Contract][' + count + '][bank_account]" type="text" maxlength="30" id="Contract' + count + 'BankAccount"></div></td>';
	rowData += '</tr>';
	rowData += '<tr class="education" data-education-count="' + count + '">';
	rowData += '<th>Datum zahájení</th>';
	rowData += '<td><input name="data[Contract][' + count + '][begin_date]" type="text" id="Contract' + count + 'BeginDate" class="contract-date"></td>';
	rowData += '<th>Datum ukončení</th>';
	rowData += '<td colspan="2"><input name="data[Contract][' + count + '][end_date]" type="text" id="Contract' + count + 'EndDate" class="contract-date"></td>';
	rowData += '</tr>';
	rowData += '<tr class="education" data-education-count="' + count + '">';
	rowData += '<th>Měsíc</th>';
	rowData += '<td><select name="data[Contract][' + count + '][month]" id="Contract' + count + 'Month">';
<?php foreach ($months as $index => $value) { ?>
	rowData += '<option value="<?php echo $index?>"><?php echo $value?></option>';
<?php } ?>	
	rowData += '</select></td>';
	rowData += '<th>Rok</th>';
	rowData += '<td colspan="2"><input name="data[Contract][' + count + '][year]" type="text" size="5" maxlength="4" id="Contract' + count + 'Year"></td>';
	rowData += '</tr>';
	rowData += '<tr class="education" data-education-count="' + count + '" style="display: table-row;">';
	rowData += '<th>Částka</th>';
	rowData += '<td><input name="data[Contract][' + count + '][amount]" type="text" size="5" maxlength="11" id="Contract' + count + 'Amount">&nbsp;Kč</td>';
	rowData += '<th>Daň</th>';
	rowData += '<td colspan="2"><input name="data[Contract][' + count + '][vat_vis]" type="text" value="15" size="3" disabled="disabled" id="Contract' + count + 'VatVis">&nbsp;%';
	rowData += '<input type="hidden" name="data[Contract][' + count + '][vat]" value="15" id="Contract' + count + 'Vat"></td>';
	rowData += '</tr>';

	return rowData;
}
</script>

<h1>Přidat obchodní jednání</h1>
<ul>
<?php if (isset($business_partner_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam obchodních jednání', $redirect)?></li>
<?php } ?>
</ul>

<?php echo $form->create('BusinessSession', array('url' => $this->passedArgs))?>
<table class="left_heading">
	<tr>
		<th style="width:100px">Odběratel</th>
		<td colspan="5">
		<?php
			echo $form->input('BusinessSession.purchaser_name', array('label' => false, 'type' => 'text', 'class' => 'BusinessSessionPurchaserName', 'size' => 70));
			echo $form->hidden('BusinessSession.purchaser_id');
			echo $form->error('BusinessSession.purchaser_id');
		?>
		</td>
	</tr>
	<tr>
		<th>Datum</th>
		<td><?php echo $form->input('BusinessSession.date', array('type' => 'text', 'label' => false, 'div' => false))?></td>
		<th>Od</th>
		<td><?php echo $form->input('BusinessSession.time', array('type' => 'time', 'timeFormat' => '24', 'label' => false))?></td>
		<th>Do</th>
		<td><?php echo $form->input('BusinessSession.end_time', array('type' => 'time', 'timeFormat' => '24', 'label' => false))?></td>
	</tr>
	<tr>
		<th>Typ jednání</th>
		<td colspan="5"><?php echo $form->input('BusinessSession.business_session_type_id', array('options' => $business_session_types, 'empty' => false, 'label' => false))?></td>
	</tr>
	<tr>
		<th>Popis</th>
		<td colspan="5"><?php echo $form->input('BusinessSession.description', array('label' => false, 'cols' => 70, 'rows' => 10))?></td>
	</tr>
	<tr>
		<td colspan="6">Náklady</td>
	</tr>
	<tr>
		<th colspan="2">Náklad</th>
		<th>Typ nákladu</th>
		<th style="text-align:right">Množství</th>
		<th style="text-align:right">Kč/J</th>
		<th>&nbsp;</th>
	</tr>
	<?php if (empty($this->data['BusinessSessionsCost'])) { ?>
	<?php $i = 0 ?>
	<tr class="cost" data-cost-count="<?php echo $i?>">
		<td colspan="2"><?php
			echo $this->Form->input('BusinessSessionsCost.0.name', array('label' => false, 'class' => 'BusinessSessionsCostName', 'size' => 30));
			echo $this->Form->error('BusinessSessionsCost.0.business_session_cost_item_id');
			echo $this->Form->hidden('BusinessSessionsCost.0.business_session_cost_item_id', array('label' => false));
		?></td>
		<td><?php echo $this->Form->input('BusinessSessionsCost.0.cost_type_id', array('label' => false, 'options' => $cost_types, 'empty' => false))?></td>
		<td align="right"><?php echo $this->Form->input('BusinessSessionsCost.0.quantity', array('label' => false, 'size' => 3))?></td>
		<td align="right"><?php echo $this->Form->input('BusinessSessionsCost.0.price', array('label' => false, 'size' => 5))?></td>
		<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>
	</tr>
	<?php } else { ?>
	<?php 	foreach ($this->data['BusinessSessionsCost'] as $index => $data) { ?>
	<tr class="cost" data-cost-count="<?php echo $index?>">
		<td colspan="2"><?php
			echo $this->Form->input('BusinessSessionsCost.' . $index . '.name', array('label' => false, 'class' => 'BusinessSessionsCostName', 'size' => 30));
			echo $this->Form->error('BusinessSessionsCost.' . $index . '.business_session_cost_item_id');
			echo $this->Form->hidden('BusinessSessionsCost.' . $index . '.business_session_cost_item_id', array('label' => false));
		?></td>
		<td><?php echo $this->Form->input('BusinessSessionsCost.' . $index . '.cost_type_id', array('label' => false, 'options' => $cost_types, 'empty' => false))?></td>
		<td align="right"><?php echo $this->Form->input('BusinessSessionsCost.' . $index . '.quantity', array('label' => false, 'size' => 3))?></td>
		<td align="right"><?php echo $this->Form->input('BusinessSessionsCost.' . $index . '.price', array('label' => false, 'size' => 5))?></td>
		<td align="right"><a href="#" class="addRowButton">+</a>&nbsp;<a href="#" class="removeRowButton">-</a></td>
	</tr>
	<?php } ?>
	<?php } ?>
	<tr>
		<th>Edukace</th>
		<td colspan="5"><?php echo $this->Form->input('BusinessSession.is_education', array('label' => false, 'type' => 'checkbox'))?></td>
	</tr>
	<?php if (empty($this->data['Contract'])) { ?>
	<?php $i = 0 	?> 
	<tr class="education" data-education-count="<?php echo $i?>">
		<td colspan="6"><?php echo $i+1 ?>. edukace</td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th style="width:100px">Kontaktní osoba</th>
		<td colspan="4"><?php
			echo $this->Form->input('Contract.' . $i . '.contact_person_name', array('label' => false, 'size' => 50, 'class' => 'contact-person-name'));
			echo $this->Form->error('Contract.' . $i . '.contact_person_id');
			echo $this->Form->error('Contract.' . $i . '.number');
			echo $this->Form->error('Contract.' . $i . '.city');
			echo $this->Form->error('Contract.' . $i . '.birthday');
			echo $this->Form->error('Contract.' . $i . '.birth_certificate_number');
			echo $this->Form->hidden('Contract.' . $i . '.contact_person_id');
		?></td>
		<td rowspan="6"><a href="#" class="removeEducationRowButton">-</a></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Typ dohody</th>
		<td colspan="4"><?php echo $this->Form->input('Contract.' . $i . '.contract_type_id', array('label' => false, 'options' => $contract_types, 'empty' => false))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Platba</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.contract_payment_id', array('label' => false, 'options' => $contract_payments, 'empty' => false))?></td>
		<th>Číslo bank. účtu</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.bank_account', array('label' => false))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Datum zahájení</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.begin_date', array('label' => false, 'type' => 'text', 'class' => 'contract-date'))?></td>
		<th>Datum ukončení</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.end_date', array('label' => false, 'type' => 'text', 'class' => 'contract-date'))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Měsíc</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.month', array('label' => false, 'options' => $months))?></td>
		<th>Rok</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.year', array('label' => false, 'size' => 5))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Částka vč. DPH</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.amount_vat', array('label' => false, 'size' => 5, 'after' => '&nbsp;Kč'))?></td>
		<th>Daň</th>
		<td colspan="2"><?php
			echo $this->Form->input('Contract.' . $i . '.vat_vis', array('label' => false, 'value' => $vat, 'size' => 3, 'after' => '&nbsp;%', 'disabled' => true));
			echo $this->Form->hidden('Contract.' . $i . '.vat', array('value' => $vat));
		?></td>
	</tr>
	<?php } else { ?>
	<?php foreach ($this->data['Contract'] as $i => $data) { ?>
	<tr class="education" data-education-count="<?php echo $i?>">
		<td colspan="6"><?php echo $i+1 ?>. edukace</td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th style="width:100px">Kontaktní osoba</th>
		<td colspan="4"><?php
			echo $this->Form->input('Contract.' . $i . '.contact_person_name', array('label' => false, 'size' => 50, 'class' => 'contact-person-name'));
			echo $this->Form->error('Contract.' . $i . '.contact_person_id');
			echo $this->Form->error('Contract.' . $i . '.number');
			echo $this->Form->error('Contract.' . $i . '.city');
			echo $this->Form->error('Contract.' . $i . '.birthday');
			echo $this->Form->error('Contract.' . $i . '.birth_certificate_number');
			echo $this->Form->hidden('Contract.' . $i . '.contact_person_id');
		?></td>
		<td rowspan="6"><a href="#" class="removeEducationRowButton">-</a></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Typ dohody</th>
		<td colspan="4"><?php echo $this->Form->input('Contract.' . $i . '.contract_type_id', array('label' => false, 'options' => $contract_types, 'empty' => false))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Platba</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.contract_payment_id', array('label' => false, 'options' => $contract_payments, 'empty' => false))?></td>
		<th>Číslo bank. účtu</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.bank_account', array('label' => false))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Datum zahájení</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.begin_date', array('label' => false, 'type' => 'text', 'class' => 'contract-date'))?></td>
		<th>Datum ukončení</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.end_date', array('label' => false, 'type' => 'text', 'class' => 'contract-date'))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Měsíc</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.month', array('label' => false, 'options' => $months))?></td>
		<th>Rok</th>
		<td colspan="2"><?php echo $this->Form->input('Contract.' . $i . '.year', array('label' => false, 'size' => 5))?></td>
	</tr>
	<tr class="education" data-education-count="<?php echo $i?>">
		<th>Částka vč. DPH</th>
		<td><?php echo $this->Form->input('Contract.' . $i . '.amount_vat', array('label' => false, 'size' => 5, 'after' => '&nbsp;Kč'))?></td>
		<th>Daň</th>
		<td colspan="2"><?php
			echo $this->Form->input('Contract.' . $i . '.vat_vis', array('label' => false, 'value' => $vat, 'size' => 3, 'after' => '&nbsp;%', 'disabled' => true));
			echo $this->Form->hidden('Contract.' . $i . '.vat', array('value' => $vat));
		?></td>
	</tr>
	<?php } ?>
	<?php } ?>
</table>

<?php
	echo $form->submit('Uložit');
	echo $form->end();
?>

<ul>
<?php if (isset($business_partner_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam obchodních jednání', $redirect)?></li>
<?php } ?>
</ul>