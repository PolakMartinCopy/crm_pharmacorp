<script type="text/javascript">
	$(function() {
		// vypis kontaktnich osob pro zadaneho odberatele / obchodniho partnera
		$('#ContactPeopleList').hide();

		var model = 'Contract';
		var dateFromId = model + 'BeginDate';
		var dateToId = model + 'EndDate';
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

		var businessPartnerId = false;
<?		if (isset($this->params['named']['business_partner_id'])) { ?>
			businessPartnerId = <?php echo $this->params['named']['business_partner_id'] ?>;
<?		} ?>

		var contactPeopleAutocompleteURL = '/user/contact_people/autocomplete_list';
		if (businessPartnerId) {
			contactPeopleAutocompleteURL = contactPeopleAutocompleteURL + '/' + businessPartnerId;

			$.ajax({
				url: '/business_partners/contact_people/' + businessPartnerId,
				dataType: 'json',
				success: function(data) {
					if (data.success) {
						var contactPeopleList = drawContactPeopleList(data.data);
						$('#ContactPeopleList').append(contactPeopleList);
						$('#ContactPeopleList').show();
					} else {
						alert(data.message);
					}
				},
				error: function(jqXHR, errorThrown, textStatus) {
					alert(textStatus);
				}
			});
		}
		
		$('#ContractContactPersonName').autocomplete({
			delay: 500,
			minLength: 2,
			source: contactPeopleAutocompleteURL,
			select: function(event, ui) {
				$('#ContractContactPersonName').val(ui.item.label);
				$('#ContractContactPersonId').val(ui.item.value);
				return false;
			}
		});
	});

	function drawContactPeopleList(contactPeople) {
		var list = '';
		if (contactPeople.length == 0) {
			list = '<p><em>Nejsou dostupné žádné kontaktní osoby pro vložení.</em></p>';
		} else {
			list = '<ul>';
			$.each(contactPeople, function(index, contactPerson) {
				list += '<li>' + contactPerson.label + '</li>';
			});
			list += '</ul>';
		}
		return list;
	}
</script>

<h1>Přidat dohodu</h1>
<ul>
<?php if (isset($this->params['named']['contact_person_id'])) { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } elseif (isset($this->params['named']['business_partner_id'])) { ?>
	<li><?php echo $html->link('Zpět na seznam obchodních partnerů', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam dohod', $redirect)?></li>
<?php } ?>
</ul>

<div id="ContactPeopleList">
	<h3>Prosím zadejte jednu z těchto kontaktních osob:</h3>
</div>

<?php echo $this->Form->create('Contract', array('url' => $this->passedArgs));?>
<table class="left_heading">
	<tr>
		<th style="width:100px">Kontaktní osoba</th>
		<td colspan="3"><?php
			if (isset($contact_person)) {
				echo $this->Form->input('Contract.contact_person_name', array('label' => false, 'size' => 50, 'disabled' => true));
			} else {
				echo $this->Form->input('Contract.contact_person_name', array('label' => false, 'size' => 50));
				echo $this->Form->error('Contract.contact_person_id');
			}
			echo $this->Form->error('Contract.number');
			echo $this->Form->error('Contract.city');
			echo $this->Form->error('Contract.birthday');
			echo $this->Form->error('Contract.birth_certificate_number');
			echo $this->Form->hidden('Contract.contact_person_id');
		?></td>
	</tr>
	<tr>
		<th>Typ dohody</th>
		<td colspan="3"><?php echo $this->Form->input('Contract.contract_type_id', array('label' => false, 'options' => $contract_types, 'empty' => false))?></td>
	</tr>
	<tr>
		<th>Platba</th>
		<td><?php echo $this->Form->input('Contract.contract_payment_id', array('label' => false, 'options' => $contract_payments, 'empty' => false))?></td>
		<th>Číslo bank. účtu</th>
		<td><?php echo $this->Form->input('Contract.bank_account', array('label' => false))?></td>
	</tr>
	<tr>
		<th>Datum zahájení</th>
		<td><?php echo $this->Form->input('Contract.begin_date', array('label' => false, 'type' => 'text'))?></td>
		<th>Datum ukončení</th>
		<td><?php echo $this->Form->input('Contract.end_date', array('label' => false, 'type' => 'text'))?></td>
	</tr>
	<tr>
		<th>Měsíc</th>
		<td><?php echo $this->Form->input('Contract.month', array('label' => false, 'options' => $months))?></td>
		<th>Rok</th>
		<td><?php echo $this->Form->input('Contract.year', array('label' => false, 'size' => 5))?></td>
	</tr>
	<tr>
		<th>Částka vč. DPH</th>
		<td><?php echo $this->Form->input('Contract.amount_vat', array('label' => false, 'size' => 5, 'after' => '&nbsp;Kč'))?></td>
		<th>Daň</th>
		<td><?php echo $this->Form->input('Contract.contract_tax_id', array('label' => false, 'options' => $contract_taxes, 'empty' => false, 'after' => '&nbsp;%'));?></td>
	</tr>
</table>
<?php echo $this->Form->submit('Uložit')?>
<?php echo $this->Form->end()?>

<ul>
<?php if (isset($this->params['named']['contact_person_id'])) { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } elseif (isset($this->params['named']['business_partner_id'])) { ?>
	<li><?php echo $html->link('Zpět na seznam obchodních partnerů', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam dohod', $redirect)?></li>
<?php } ?>
</ul>