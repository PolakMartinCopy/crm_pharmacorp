<h1>Přidat odběratele</h1>
<?php echo $this->Form->create('Purchaser', array('url' => $this->passedArgs)); ?>
<script type="text/javascript">
	$(function() {
		$('#PurchaserBusinessPartnerName').autocomplete({
			delay: 500,
			minLength: 2,
			source: '/user/business_partners/autocomplete_list',
			select: function(event, ui) {
				$('#PurchaserBusinessPartnerName').val(ui.item.label);
				$('#PurchaserBusinessPartnerId').val(ui.item.value);
				return false;
			}
		});

		$('#PurchaserBusinessPartnerAddress').click(function () {
			if ($(this).is(':checked')) {
				var businessPartnerId = $('#PurchaserBusinessPartnerId').val();
				if (businessPartnerId != '') {
					$.ajax({
						url: '/business_partners/address/' + businessPartnerId,
						dataType: 'json',
						success: function(data) {
							if (data.success) {
								$('#AddressStreet').val(data.data.Address.street);
								$('#AddressNumber').val(data.data.Address.number);
								$('#AddressONumber').val(data.data.Address.o_number);
								$('#AddressCity').val(data.data.Address.city);
								$('#AddressZip').val(data.data.Address.zip);
								$('#AddressRegion').val(data.data.Address.region);
							} else {
								alert(data.message);
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							alert(textStatus);
						} 
					});
				}
			}
		});	
	});
</script>
		
<table class="left_heading">
	<tr>
		<th>Obchodní partner<sup>*</sup></th>
		<td colspan="7"><?php
			if (isset($purchaser)) {
				echo $this->Form->input('Purchaser.business_partner_name', array('label' => false, 'size' => 70, 'disabled' => true));
			} else {
				echo $this->Form->input('Purchaser.business_partner_name', array('label' => false, 'size' => 70));
			}
			echo $this->Form->hidden('Purchaser.business_partner_id');
		?></td>
	</tr>
	<tr>
		<th>Titul před</th>
		<td><?php echo $this->Form->input('Purchaser.degree_before', array('label' => false, 'size' => 10))?></td>
		<th>Jméno</th>
		<td><?php echo $form->input('Purchaser.first_name', array('label' => false, 'size' => 15))?></td>
		<th>Příjmení</th>
		<td><?php echo $form->input('Purchaser.last_name', array('label' => false, 'size' => 20))?></td>
		<th>Titul za</th>
		<td><?php echo $this->Form->input('Purchaser.degree_after', array('label' => false, 'size' => 10))?></td>
	</tr>
	<tr>
		<th>Telefon</th>
		<td><?php echo $this->Form->input('Purchaser.phone', array('label' => false, 'size' => 15))?></td>
		<th>Email</th>
		<td colspan="3"><?php echo $this->Form->input('Purchaser.email', array('label' => false, 'size' => 40))?></td>
		<th>IČZ</th>
		<td><?php echo $this->Form->input('Purchaser.icz', array('label' => false, 'size' => 15))?></td>
	</tr>
	
	<tr>
		<th>Bonita</th>
			<td>
				
				<table>
					<tr>
						<th>&nbsp;</th>
						<th>A</th>
						<th>B</th>
						<th>C</th>
					</tr>
					<tr>
						<th>1</th>
						<td><input type="radio" name="data[Purchaser][bonity]" value="1" id="PurchaserBonity1"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 1 ? ' checked="checked"' : '') ?>></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="4" id="PurchaserBonity4"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 4 ? ' checked="checked"' : '') ?>></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="7" id="PurchaserBonity7"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 7 ? ' checked="checked"' : '') ?>></td>
					</tr>
					<tr>
						<th>2</th>
						<td><input type="radio" name="data[Purchaser][bonity]" value="2" id="PurchaserBonity2"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 2 ? ' checked="checked"' : '') ?>/></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="5" id="PurchaserBonity5"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 5 ? ' checked="checked"' : '') ?>/></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="8" id="PurchaserBonity8"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 8 ? ' checked="checked"' : '') ?>/></td>
					</tr>
					<tr>
						<th>3</th>
						<td><input type="radio" name="data[Purchaser][bonity]" value="3" id="PurchaserBonity3"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 3 ? ' checked="checked"' : '') ?>/></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="6" id="PurchaserBonity6"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 6 ? ' checked="checked"' : '') ?>/></td>
						<td><input type="radio" name="data[Purchaser][bonity]" value="9" id="PurchaserBonity9"<?php echo (isset($this->data['Purchaser']['bonity']) && $this->data['Purchaser']['bonity'] == 9 ? ' checked="checked"' : '') ?>/></td>
					</tr>
				</table>
			</td>
		<th>Poznámka</th>
		<td colspan="5"><?php echo $this->Form->input('Purchaser.note', array('label' => false, 'cols' => 70, 'rows' => 5))?></td>
	</tr>
	<tr>
		<th>Počet pacientů</th>
		<td><?php echo $this->Form->input('Purchaser.patient_count', array('label' => false, 'size' => 5))?></td>
		<th>Kategorie</th>
		<td><?php echo $this->Form->input('Purchaser.category', array('label' => false, 'size' => 1))?></td>
		<th>Typ edukace</th>
		<td><?php echo $this->Form->input('Purchaser.education_type_id', array('label' => false, 'options' => $education_types, 'empty' => false))?></td>
		<th>Typ odběratele</th>
		<td><?php echo $this->Form->input('Purchaser.purchaser_type_id', array('label' => false, 'options' => $purchaser_types, 'empty' => false))?></td>
	</tr>
	<tr>
		<td colspan="8">Doručovací adresa</td>
	</tr>
	<tr>
		<th colspan="4">Shodná s adresou obchodního partnera</th>
		<td colspan="4"><?php echo $this->Form->input('Purchaser.business_partner_address', array('label' => false, 'type' => 'checkbox'))?></td>
	</tr>
	<tr>
		<th>Ulice</th>
		<td colspan="3"><?php echo $form->input('Address.street', array('label' => false, 'size' => 30))?></td>
		<th>Č. pop.<sup>*</sup></th>
		<td><?php echo $form->input('Address.number', array('label' => false, 'size' => 10))?></td>
		<th>Č. or.</th>
		<td><?php echo $form->input('Address.o_number', array('label' => false, 'size' => 10))?></td>
	</tr>
	<tr>
		<th>Město<sup>*</sup></th>
		<td colspan="3"><?php echo $form->input('Address.city', array('label' => false, 'size' => 30))?></td>
		<th>PSČ</th>
		<td colspan="3"><?php echo $form->input('Address.zip', array('label' => false, 'size' => 10))?></td>
	</tr>
	<tr>
		<th>Okres</th>
		<td colspan="7"><?php echo $form->input('Address.region', array('label' => false, 'size' => 30))?></td>
	</tr>
</table>
<?php 
	echo $this->Form->hidden('Purchaser.user_id', array('value' => $user_id));
	echo $this->Form->hidden('Purchaser.active', array('value' => 'true'));
	echo $this->Form->hidden('Address.0.address_type_id', array('value' => 4));
	echo $this->Form->submit('Uložit');
	echo $this->Form->end();
?>