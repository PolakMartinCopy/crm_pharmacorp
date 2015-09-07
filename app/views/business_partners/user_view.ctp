<?php
if (isset($this->params['named']['tab'])) {
	$tab_pos = $this->params['named']['tab'];
?>
	<script type="text/javascript">
		$(function() {
			$( "#tabs" ).tabs("select", "#tabs-<?php echo $tab_pos?>");
		});
	</script>
<?php } ?>

<h1><?php echo $business_partner['BusinessPartner']['name']?></h1>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Info</a></li>
		<li><a href="#tabs-3">Odběratelé</a>
		<li><a href="#tabs-13">Poznámky</a></li>
	</ul>
	
<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-1">
		<h2>Základní informace</h2>
<table class="left_heading" style="width:100%">
	<tr>
		<th>Název</th>
		<td colspan="7"><?php echo $business_partner['BusinessPartner']['name']?></td>
	</tr>
	<tr>
		<th>Titul před</th>
		<td><?php echo $business_partner['BusinessPartner']['degree_before']?></td>
		<th>Jméno</th>
		<td><?php echo $business_partner['BusinessPartner']['first_name']?></td>
		<th>Příjmení</th>
		<td><?php echo $business_partner['BusinessPartner']['last_name']?></td>
		<th>Titul za</th>
		<td><?php echo $business_partner['BusinessPartner']['degree_after']?></td>
	</tr>
	<tr>
		<th>IČ</th>
		<td colspan="3"><?php echo $business_partner['BusinessPartner']['ico']?></td>
		<th>DIČ</th>
		<td colspan="3"><?php echo $business_partner['BusinessPartner']['dic']?></td>
	</tr>
	<tr>
		<th>Email</th>
		<td colspan="3"><?php echo $business_partner['BusinessPartner']['email']?></td>
		<th>Telefon</th>
		<td colspan="3"><?php echo $business_partner['BusinessPartner']['phone']?></td>
	</tr>
	<tr>
		<th>Stav účtu</th>
		<td colspan="7"><?php echo $business_partner['BusinessPartner']['wallet']?>&nbsp;Kč</td>
	</tr>
	<tr>
		<th>Poznámka</th>
		<td colspan="7"><?php echo str_replace("\n", '<br/>', $business_partner['BusinessPartner']['note'])?></td>
	</tr>
	<tr>
		<th>Provozní doba</th>
		<td colspan="7"><?php echo str_replace("\n", '<br/>', $business_partner['BusinessPartner']['opening_hours'])?></td>
	</tr>
	<tr>
		<td colspan="8">Adresa sídla</td>
	</tr>
	<tr>
		<th>Ulice</th>
		<td colspan="3"><?php echo $seat_address['Address']['street']?></td>
		<th>Č. pop.</th>
		<td><?php echo $seat_address['Address']['number']?></td>
		<th>Č. or.</th>
		<td><?php echo $seat_address['Address']['o_number']?></td>
	</tr>
	<tr>
		<th>Město</th>
		<td colspan="3"><?php echo $seat_address['Address']['city']?></td>
		<th>PSČ</th>
		<td colspan="3"><?php echo $seat_address['Address']['zip']?></td>
	</tr>
	<tr>
		<th>Okres</th>
		<td colspan="7"><?php echo $seat_address['Address']['region']?></td>
	</tr>
</table>
		<ul>
			<li><?php echo $html->link('Upravit obchodního partnera', array('controller' => 'business_partners', 'action' => 'edit', $business_partner['BusinessPartner']['id'], 'back_link' => base64_encode(serialize(array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id'], 'tab' => 1)))))?>
		</ul>
	</div>
<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-3">
		<h2>Odběratelé</h2>
		<?php 
			echo $this->element('search_forms/purchasers', array('url' => array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id'], 'tab' => 3)));
			
			echo $form->create('CSV', array('url' => array('controller' => 'purchasers', 'action' => 'xls_export')));
			echo $form->hidden('data', array('value' => serialize($purchasers_find)));
			echo $form->hidden('fields', array('value' => serialize($purchasers_export_fields)));
			echo $form->submit('CSV');
			echo $form->end();
		?>

		<?php if (empty($purchasers)) { ?>
		<p><em>Obchodní partner nemá zadané žádné odběratele.</em></p>
		<?php } else {
		$paginator->options(array(
			'url' => array('tab' => 3, 0 => $business_partner['BusinessPartner']['id'])
		));
		$paginator->params['paging'] = $purchasers_paging;
		$paginator->__defaultModel = 'Purchaser';
		echo $this->element('indexes/purchasers');
		} // end if ?>
	</div>
<?php /* TAB 13 ****************************************************************************************************************/ ?>
	<div id="tabs-13">
		<h2>Poznámky</h2>
		<?php echo $this->Form->create('BusinessPartnerNote', array('action' => 'add'))?>
		<table>
			<tr>
				<td><?php echo $this->Form->input('BusinessPartnerNote.text', array('label' => false, 'cols' => 70, 'rows' => 5))?></td>
				<td>
					<?php echo $this->Form->hidden('BusinessPartnerNote.business_partner_id', array('value' => $business_partner['BusinessPartner']['id']))?>
					<?php echo $this->Form->submit('Uložit')?>
				</td>
			</tr>
		</table>
		<?php echo $this->Form->end()?>
		
		<?php if (empty($business_partner_notes)) { ?>
		<p><em>žádné poznámky</em></p>
		<?php } else { ?>
			<table class="top_heading">
				<tr>
					<th>Datum</th>
					<th>Poznámka</th>
					<th>&nbsp;</th>
				</tr>
			<?php foreach ($business_partner_notes as $note) {?>
			<tr>
				<td><?php echo $note['BusinessPartnerNote']['created']?></td>
				<td><?php echo $note['BusinessPartnerNote']['text']?></td>
				<td><?php 
					echo $this->Html->link('Upravit', array('controller' => 'business_partner_notes', 'action' => 'edit', $note['BusinessPartnerNote']['id'])) . ' | ';
					echo $this->Html->link('Smazat', array('controller' => 'business_partner_notes', 'action' => 'delete', $note['BusinessPartnerNote']['id']), null, 'Opravdu chcete poznámku odstranit?');
				?></td>
			</tr>
			<?php } ?>
			</table>
		<?php } ?>
	</div>
</div>