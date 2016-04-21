<script>
	$(document).ready(function(){
		$('#ContactPersonPurchaserName').autocomplete({
			delay: 500,
			minLength: 2,
			source: '/user/purchasers/autocomplete_list',
			select: function(event, ui) {
				purchaserId = ui.item.value;
				$('#ContactPersonPurchaserName').val(ui.item.label);
				$('#ContactPersonPurchaserId').val(ui.item.value);
				return false;
			}
		});

		$("#ContactPersonBirthday").datepicker({
			changeYear: true,
			yearRange: '1920:<?php echo date('Y')?>',
			numberOfMonths: 1
		});
	});
</script>

<h1>Upravit kontaktní osobu</h1>
<ul>
<?php if (isset($purchaser_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } ?>
</ul>

<?php
if (isset($purchaser_id)) {
	echo $form->create('ContactPerson', array('url' => array('controller' => 'contact_people', 'action' => 'edit', 'purchaser_id' => $purchaser_id) + $this->passedArgs));
} else {
	echo $form->create('ContactPerson', array('url' => array('controller' => 'contact_people', 'action' => 'edit') + $this->passedArgs));
}
?>
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
	});
</script>

<table class="left_heading">
	<tr>
		<th>Odběratel<sup>*</sup></th>
		<td colspan="7"><?php
			echo $form->input('ContactPerson.purchaser_name', array('label' => false, 'type' => 'text', 'class' => 'ContactPersonPurchaserName', 'size' => 70));
			echo $form->error('ContactPerson.purchaser_id');
			if (!empty($this->data['ContactPerson']['purchaser_id'])) {
				echo $form->hidden('ContactPerson.purchaser_id_old', array('value' => $this->data['ContactPerson']['purchaser_id']));
				$this->data['ContactPerson']['purchaser_id_old'] = $this->data['ContactPerson']['purchaser_id'];
			}
			if (!empty($this->data['ContactPerson']['purchaser_id_old'])) {
				echo $form->hidden('ContactPerson.purchaser_id_old', array('value' => $this->data['ContactPerson']['purchaser_id_old']));
			}
		 ?></td>
	<tr>
		<th>Titul před</th>
		<td><?php echo $this->Form->input('ContactPerson.degree_before', array('label' => false, 'size' => 10))?></td>
		<th>Jméno<sup>*</sup></th>
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
		<th>Číslo bank. účtu</th>
		<td colspan="3"><?php echo $this->Form->input('ContactPerson.bank_account', array('label' => false))?></td>
		<th>Datum narození</th>
		<td><?php echo $this->Form->input('ContactPerson.birthday', array('label' => false, 'type' => 'text'))?></td>
		<th>Rodné číslo</th>
		<td><?php echo $this->Form->input('ContactPerson.birth_certificate_number', array('label' => false, 'size' => 10))?></td>
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
	<tr>
		<th>Okres</th>
		<td colspan="7"><?php echo $form->input('Address.region', array('label' => false, 'size' => 30))?></td>
	</tr>
</table>
<?php echo $form->hidden('Address.id')?>
<?php echo $form->hidden('ContactPerson.id')?>
<?php echo $form->submit('Upravit')?>
<?php echo $form->end()?>

<ul>
<?php if (isset($purchaser_id)) { ?>
	<li><?php echo $html->link('Zpět na detail odběratele', $redirect)?></li>
<?php } else { ?>
	<li><?php echo $html->link('Zpět na seznam kontaktních osob', $redirect)?></li>
<?php } ?>
</ul>