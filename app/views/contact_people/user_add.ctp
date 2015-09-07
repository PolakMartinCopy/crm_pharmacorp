<h1>Přidat kontaktní osobu</h1>
<ul>
<?php if (isset($purchaser_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } ?>
</ul>



<?php echo $form->create('ContactPerson', array('url' => array('controller' => 'contact_people', 'action' => 'add') + $this->passedArgs)); ?>
<script>
	$(document).ready(function(){
		data = <?php echo $purchasers?>;
		$('input.ContactPersonPurchaserName').each(function() {
			var autoCompelteElement = this;
			var formElementName = $(this).attr('name');
			var formElementId = $(this).attr('id');
			var hiddenElementID  = 'ContactPersonPurchaserId';
			var hiddenElementName = 'data[ContactPerson][purchaser_id]';
			/* create new hidden input with name of orig input */
			$(this).after("<input type=\"hidden\" name=\"" + hiddenElementName + "\" id=\"" + hiddenElementID + "\" />");
			$(this).autocomplete({
				source: data, 
				select: function(event, ui) {
					var selectedObj = ui.item;
					$(autoCompelteElement).val(selectedObj.label);
					$('#'+hiddenElementID).val(selectedObj.value);
					return false;
				}
			});
		});

		$("#ContactPersonBirthday").datepicker({
			changeYear: true,
			yearRange: '1920:<?php echo date('Y')?>',
			numberOfMonths: 1
		});
	});
</script>

<table class="left_heading">
	<tr>
		<th>Odběratel<sup>*</sup></th>
		<td colspan="7"><?php
			if (isset($purchaser_id)) {
				echo $form->input('ContactPerson.purchaser_id', array('options' => $purchasers, 'selected' => $purchaser_id, 'empty' => false, 'label' => false, 'disabled' => true));
				echo $form->hidden('ContactPerson.purchaser_id', array('value' => $purchaser_id));
			} else {
				echo $form->input('ContactPerson.purchaser_name', array('label' => false, 'type' => 'text', 'class' => 'ContactPersonPurchaserName'));
				echo $form->error('ContactPerson.purchaser_id');
			}
	?></td>
	<tr>
		<th>Titul před</th>
		<td><?php echo $this->Form->input('ContactPerson.degree_before', array('label' => false, 'size' => 10))?></td>
		<th>Jméno</th>
		<td><?php echo $this->Form->input('ContactPerson.first_name', array('label' => false, 'size' => 20))?></td>
		<th>Příjmení<sup>*</sup></th>
		<td><?php echo $this->Form->input('ContactPerson.last_name', array('label' => false, 'size' => 30))?></td>
		<th>Titul za</th>
		<td><?php echo $this->Form->input('ContactPerson.degree_after', array('label' => false, 'size' => 10))?></td>
	</tr>
	<tr>
		<th>Email</th>
		<td colspan="3"><?php echo $form->input('ContactPerson.email', array('label' => false, 'size' => 30))?></td>
		<th>Telefon<sup>*</sup></th>
		<td><?php echo $form->input('ContactPerson.phone', array('label' => false, 'size' => 10))?></td>
		<th>Mobil</th>
		<td><?php echo $form->input('ContactPerson.cellular', array('label' => false, 'size' => 10))?></td>
	</tr>
	<tr>
		<th>Datum narození</th>
		<td colspan="3"><?php echo $this->Form->input('ContactPerson.birthday', array('label' => false, 'type' => 'text'))?></td>
		<th>Rodné číslo</th>
		<td colspan="3"><?php echo $this->Form->input('ContactPerson.birth_certificate_number', array('label' => false, 'size' => 15))?></td>
	</tr>
	<tr>
		<th>Poznámka</th>
		<td colspan="7"><?php echo $form->input('ContactPerson.note', array('label' => false, 'rows' => 5, 'cols' => 70))?></td>
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
</table>
<?php echo $form->submit('Uložit')?>
<?php echo $form->end()?>

<ul>
<?php if (isset($purchaser_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } ?>
</ul>
