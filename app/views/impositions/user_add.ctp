<script>
	$(document).ready(function(){
		data = <?php echo $purchasers?>;
		$('input.ImpositionPurchaserName').each(function() {
			var autoCompelteElement = this;
			var formElementName = $(this).attr('name');
			var formElementId = $(this).attr('id');
			var hiddenElementID  = 'ImpositionPurchaserId';
			var hiddenElementName = 'data[Imposition][purchaser_id]';
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

	$(function() {
		var dates = $( "#SolutionAccomplishmentDate, #RecursiveImpositionFrom, #RecursiveImpositionTo" ).datepicker({
			defaultDate: "+1w",
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
				var option = this.id == "RecursiveImpositionFrom" ? "minDate" : "maxDate",
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

<h1>Přidat úkol</h1>

<?php echo $form->create('Imposition', array('url' => array('controller' => 'impositions', 'action' => 'add')))?>
<table class="left_heading">
	<tr>
		<th>Odběratel</th>
		<td><?php 
			echo $form->input('Imposition.purchaser_name', array('label' => false, 'type' => 'text', 'class' => 'ImpositionPurchaserName', 'size' => 70));
			echo $form->error('Imposition.purchaser_id');
			if (!empty($this->data['Imposition']['purchaser_id'])) {
				echo $form->hidden('Imposition.purchaser_id_old', array('value' => $this->data['Imposition']['purchaser_id']));
				$this->data['Imposition']['purchaser_id_old'] = $this->data['Imposition']['purchaser_id'];
			}
			if (!empty($this->data['Imposition']['purchaser_id_old'])) {
				echo $form->hidden('Imposition.purchaser_id_old', array('value' => $this->data['Imposition']['purchaser_id_old']));
			}
		?></td>
	</tr>
	<tr>
		<th>Předmět</th>
		<td><?php echo $form->input('Imposition.title', array('label' => false, 'size' => 70))?></td>
	</tr>
	<tr>
		<th>Popis</th>
		<td><?php echo $form->input('Imposition.description', array('label' => false, 'cols' => '100%', 'rows' => 15))?></td>
	</tr>
	<tr>
		<th>Řešitelé</th>
		<td><?php echo $form->input('ImpositionsUser.user_id', array('multiple' => true, 'label' => false, 'options' => $users, 'empty' => false))?></td>
	</tr>
	<tr>
		<th>Termín splnění</th>
		<td><?php echo $form->input('Solution.accomplishment_date', array('label' => false, 'type' => 'text'))?></td>
	</tr>
	<tr>
		<th>Rekurzivní?</th>
		<td>
			<?php echo $form->input('Imposition.recursive', array('type' => 'checkbox', 'label' => false, 'value' => (isset($this->data['Imposition']['recursive'])) ? $this->data['Imposition']['recursive'] : false, 'div' => false))?>
			<?php echo $form->input('RecursiveImposition.imposition_period_id', array('label' => false, 'options' => $period_options, 'empty' => false, 'div' => false))?>
		</td>
	</tr>
	<tr>
		<th>Od</th>
		<td><?php echo $form->input('RecursiveImposition.from', array('label' => false, 'type' => 'text'))?></td>
	</tr>
	<tr>
		<th>Do</th>
		<td><?php
			echo $form->input('RecursiveImposition.to_check', array('type' => 'checkbox', 'checked' => (isset($this->data['RecursiveImposition']['to_check'])) ? $this->data['RecursiveImposition']['to_check'] : false, 'value' => true, 'div' => false, 'label' => false));
			echo $form->input('RecursiveImposition.to', array('label' => false, 'div' => false, 'type' => 'text'))
		?></td>
	</tr>
</table>
<?php
	echo $form->hidden('Imposition.user_id', array('value' => $user_id));
	echo $form->hidden('Imposition.imposition_state_id', array('value' => 1));
	echo $form->submit('Uložit');
	echo $form->end();
?>