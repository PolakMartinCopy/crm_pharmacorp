<div class="menu_header">
	Obchodní partner
</div>
<ul class="menu_links">
	<li><?php echo $html->link('Detail obchodního partnera', array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id']))?></li>
	<li><?php echo $html->link('Upravit obchodního partnera', array('controller' => 'business_partners', 'action' => 'edit', $business_partner['BusinessPartner']['id']))?></li>
	<li><?php echo $html->link('Smazat obchodního partnera', array('controller' => 'business_partners', 'action' => 'delete', $business_partner['BusinessPartner']['id']), null, 'Opravdu chcete tohoto obchodního partnera odstranit?')?>
	<li><?php echo $html->link('Upravit adresu sídla', array('controller' => 'addresses', 'action' => 'edit', $seat_address['Address']['id']))?></li>
	<li><?php echo $html->link('Přidat odběratele', array('controller' => 'purchasers', 'action' => 'add', 'business_partner_id' => $business_partner['BusinessPartner']['id']))?></li>
</ul>
