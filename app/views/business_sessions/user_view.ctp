<?php
if (isset($this->params['named']['tab'])) {
	$tab_pos = $this->params['named']['tab'];
?>
	<script>
		$(function() {
			$( "#tabs" ).tabs("select", "#tabs-<?php echo $tab_pos?>");
		});
	</script>
<?php } ?>


<h1>Detail obchodního jednání</h1>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Info</a></li>
		<li><a href="#tabs-3">Náklady</a></li>
		<li><a href="#tabs-2">Dohody</a></li>
	</ul>
		
<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-1">
		<h2>Základní informace</h2>
		<table class="left_heading">
			<tr>
				<th>ID</th>
				<td><?php echo $business_session['BusinessSession']['id']?></td>
			</tr>
			<tr>
				<th>Obchodní partner</th>
				<td><?php echo $html->link($business_session['Purchaser']['name'], array('controller' => 'business_partners', 'action' => 'view', $business_session['Purchaser']['id']))?></td>
			</tr>
			<tr>
				<th>Uživatel</th>
				<td><?php echo $business_session['User']['first_name'] . ' ' . $business_session['User']['last_name']?></td>
			</tr>
			<tr>
				<th>Datum uskutečnění</th>
				<td><?php echo $business_session['BusinessSession']['date']?></td>
			</tr>
			<tr>
				<th>Datum vložení</th>
				<td><?php echo $business_session['BusinessSession']['created']?></td>
			</td>
			<tr>
				<th>Typ jednání</th>
				<td><?php echo $business_session['BusinessSessionType']['name']?></td>
			</tr>
			<tr>
				<th>Stav jednání</th>
				<td><?php echo $business_session['BusinessSessionState']['name']?></td>
			</tr>
			<tr>
				<th>Popis</th>
				<td><?php echo $business_session['BusinessSession']['description']?></td>
			</tr>
		</table>
	</div>
<?php /* TAB 3 ****************************************************************************************************************/ ?>
	<div id="tabs-3">
		<h2>Náklady</h2>
		<button id="search_form_show_business_sessions_costs">vyhledávací formulář</button>
		<?php
			$hide = ' style="display:none"';
			if ( isset($this->data['BusinessSessionsCostForm']) ){
				$hide = '';
			}
		?>
		<div id="search_form_business_sessions_costs"<?php echo $hide?>>
			<?php echo $form->create('Cost', array('url' => array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id'], 'tab' => 3))); ?>
			<table class="left_heading">
				<tr>
					<th>Název</th>
					<td colspan="5"><?php echo $form->input('BusinessSessionsCostForm.BusinessSessionsCost.name', array('label' => false))?></td>
				<tr>
					<td colspan="6"><?php echo $html->link('reset filtru', array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id'], 'reset:business_sessions_costs'))?></td>
				</tr>
			</table>
			<?php
				echo $form->hidden('BusinessSessionsCostForm.BusinessSessionsCost.search_form', array('value' => 1));
				echo $form->submit('Vyhledávat');
				echo $form->end();
			?>
		</div>
		
		<script>
			$("#search_form_show_business_sessions_costs").click(function () {
				if ($('#search_form_business_sessions_costs').css('display') == "none"){
					$("#search_form_business_sessions_costs").show("slow");
				} else {
					$("#search_form_business_sessions_costs").hide("slow");
				}
			});
		
			$(function() {
				var dates = $( "#BusinessSessionsCostFormCostDate" ).datepicker({
					changeMonth: false,
					numberOfMonths: 1,
					onSelect: function( selectedDate ) {
						var option = this.id == "BusinessSessionsCostFormCostDate" ? "minDate" : "maxDate",
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
		<?php
		echo $form->create('CSV', array('url' => array('controller' => 'business_sessions_costs', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($costs_find)));
		echo $form->hidden('fields', array('value' => serialize($costs_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		
		if (empty($business_session['BusinessSessionsCost'])) {
		?>
		<p><em>K tomuto jednání se nevztahují žádné náklady.</em></p>
		<?php } else { ?>
		<table class="top_heading">
			<tr>
				<th>ID</th>
				<th>Název</th>
				<th>Typ nákladu</th>
				<th align="right">Množství</th>
				<th align="right">Kč/J</th>
			</tr>
		<?php
			$odd = '';
			foreach ($business_session['BusinessSessionsCost'] as $cost) {
				$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
		?>
			<tr<?php echo $odd?>>
				<td><?php echo $cost['id']?></td>
				<td><?php echo $cost['name']?></td>
				<td><?php echo $cost['CostType']['name'] ?></td>
				<td align="right"><?php echo $cost['quantity']?></td>
				<td align="right"><?php echo $cost['price']?></td>
			</tr>
		<?php } ?>
		</table>
		<?php } ?>
	</div>
	
	<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-2">
		<h2>Dohody</h2>
<?php	echo $this->element('search_forms/contracts', array('url' => array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id'], 'tab' => 2)));

		echo $form->create('CSV', array('url' => array('controller' => 'contracts', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($contract_find)));
		echo $form->hidden('fields', array('value' => serialize($contract_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
?>
		<?php if (empty($contracts)) { ?>
		<p><em>Během obchodního jednání nebyly uzavřeny žádné dohody.</em></p>
		<?php } else {
		$paginator->options(array(
			'url' => array('tab' => 2, 0 => $business_session['BusinessSession']['id'])
		));
		$paginator->params['paging'] = $contract_paging;
		$paginator->__defaultModel = 'Contract';
		echo $this->element('indexes/contracts', array('back_link' => $contract_back_link));
		?>
		<?php } // end if ?>
	</div>
</div>