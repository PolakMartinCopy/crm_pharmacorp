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
		<th>Poznámka</th>
		<td colspan="7"><?php echo $form->input('ContactPerson.note', array('label' => false, 'rows' => 5, 'cols' => 70))?></td>
	</tr>
</table>