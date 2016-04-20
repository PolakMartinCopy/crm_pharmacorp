<button id="search_form_show_business_session">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['BusinessSessionSearch']) ){
		$hide = '';
	}
?>
<div id="search_form_business_session"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'business_sessions', 'action' => 'index');
	}?>
	<?php echo $form->create('BusinessSession', array('url' => $url)); ?>
	<table class="left_heading">
		<tr>
			<td colspan="6">Odběratel</td>
		</tr>
		<tr>
			<th>Příjmení</th>
			<td><?php echo $form->input('BusinessSessionSearch.Purchaser.name', array('label' => false))?></td>
			<th>Ulice</th>
			<td><?php echo $form->input('BusinessSessionSearch.Address.street', array('label' => false))?></td>
			<th>Město</th>
			<td><?php echo $form->input('BusinessSessionSearch.Address.city', array('label' => false))?></td>
		</tr>
		<tr>
			<th>IČZ</th>
			<td><?php echo $form->input('BusinessSessionSearch.Purchaser.icz', array('label' => false))?></td>
			<th>Kategorie</th>
			<td colspan="3"><?php echo $form->input('BusinessSessionSearch.Purchaser.category', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">Obchodní jednání</td>
		</tr>
		<tr>
			<th>Datum od</th>
			<td><?php echo $form->input('BusinessSessionSearch.BusinessSession.date_from', array('label' => false, 'type' => 'text'))?></td>
			<th>Datum do</th>
			<td><?php echo $form->input('BusinessSessionSearch.BusinessSession.date_to', array('label' => false, 'type' => 'text'))?></td>
			<th>Typ jednání</th>
			<td><?php echo $form->input('BusinessSessionSearch.BusinessSession.business_session_type_id', array('options' => $business_session_types, 'empty' => true, 'label' => false))?></td>
		</tr>
		<tr>
			<th>Obchodník</th>
			<td colspan="5"><?php echo $form->input('BusinessSessionSearch.BusinessSession.user_id', array('label' => false, 'options' => $users, 'empty' => true))?></td>
		</tr>
		<tr>
			<td colspan="6">
				<?php
					$reset_url = $_SERVER['REQUEST_URI'];
					if ($this->params['action'] == 'user_index' && empty($this->params['named'])) {
						$reset_url = $_SERVER['REQUEST_URI'] . '/index';
					}
					$reset_url .= '/reset:business_sessions';
					echo $html->link('reset filtru', $reset_url);
				?>
			</td>
		</tr>
	</table>
	<?php
		echo $form->hidden('BusinessSessionSearch.BusinessSession.search_form', array('value' => '1'));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_business_session").click(function () {
		if ($('#search_form_business_session').css('display') == "none"){
			$("#search_form_business_session").show("slow");
		} else {
			$("#search_form_business_session").hide("slow");
		}
	});
	$(function() {
		var dates = $( "#BusinessSessionSearchBusinessSessionDateFrom, #BusinessSessionSearchBusinessSessionDateTo" ).datepicker({
			defaultDate: "+1w",
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
				var option = this.id == "BusinessSessionSearchBusinessSessionDateFrom" ? "minDate" : "maxDate",
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