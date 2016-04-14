<table class="top_heading" style="width:100%">
	<tr>
		<th><?php echo $this->Paginator->sort('ID', 'BusinessPartner.id')?></th>
		<th><?php echo $this->Paginator->sort('Název', 'BusinessPartner.name')?></th>
		<th><?php echo $this->Paginator->sort('IČO', 'BusinessPartner.ico')?></th>
		<th><?php echo $this->Paginator->sort('DIČ', 'BusinessPartner.dic')?></th>
		<th><?php echo $this->Paginator->sort('Email', 'BusinessPartner.email')?></th>
		<th><?php echo $this->Paginator->sort('Telefon', 'BusinessPartner.phone')?></th>
		<th><?php echo $this->Paginator->sort('Ulice a č.p.', 'BusinessPartner.address_street_info')?></th>
		<th><?php echo $this->Paginator->sort('Město', 'Address.city')?></th>
		<th><?php echo $this->Paginator->sort('PSČ', 'Address.zip')?></th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($business_partners as $business_partner) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $business_partner['BusinessPartner']['id']?></td>
		<td><?php echo $html->link($business_partner['BusinessPartner']['name'], array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id']))?></td>
		<td><?php echo $business_partner['BusinessPartner']['ico']?></td>
		<td><?php echo $business_partner['BusinessPartner']['dic']?></td>
		<td><?php echo $business_partner['BusinessPartner']['email']?></td>
		<td><?php echo $business_partner['BusinessPartner']['phone']?></td>
		<td><?php echo $business_partner['BusinessPartner']['address_street_info']?></td>
		<td><?php echo $business_partner['Address']['city']?></td>
		<td><?php echo $business_partner['Address']['zip']?></td>
		<td class="actions"><?php 
			$links = array();
			$links[] = $html->link('Detail', array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id']));
			$links[] = $html->link('Upravit', array('controller' => 'business_partners', 'action' => 'edit', $business_partner['BusinessPartner']['id']));
			$links[] = $this->Html->link('Zadat dohodu', array('controller' => 'contracts', 'action' => 'add', 'business_partner_id' => $business_partner['BusinessPartner']['id'], 'back_link' => base64_encode(serialize(array('controller' => 'business_partners', 'action' => 'index')))));
			$links[] = $html->link('Smazat', array('controller' => 'business_partners', 'action' => 'delete', $business_partner['BusinessPartner']['id']), null, 'Opravdu chcete smazat obchodního partnera se vším, co k němu náleží?');
			echo implode('&nbsp;| ', $links);
		?></td>
	</tr>
<?php } // end foreach?>
</table>