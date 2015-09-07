<div class="menu_header">
	Odběratel
</div>
<ul class="menu_links">
	<li><?php echo $html->link('Detail odběratele', array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id']))?></li>
	<li><?php echo $html->link('Upravit odběratele', array('controller' => 'purchasers', 'action' => 'edit', $purchaser['Purchaser']['id']))?></li>
	<li><?php echo $html->link('Smazat odběratele', array('controller' => 'purchasers', 'action' => 'delete', $purchaser['Purchaser']['id']), null, 'Opravdu chcete tohoto odběratele odstranit?')?>
	<li><?php echo $html->link('Upravit adresu sídla', '#')?></li>
	<li><?php echo $html->link('Přidat kontaktní osobu', array('controller' => 'contact_people', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id']))?></li>
	<li><?php echo $html->link('Přidat obchodní jednání', array('controller' => 'business_sessions', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id']))?></li>
	<li><?php echo $this->Html->link('Přidat dodací list', array('controller' => 'delivery_notes', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id']))?></li>
	<li><?php echo $this->Html->link('Přidat poukaz', array('controller' => 'sales', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id']))?></li>
<?php if ($acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Purchasers/user_edit_user')) { ?>
	<li><?php echo $html->link('Upravit uživatele', array('controller' => 'purchasers', 'action' => 'edit_user', $purchaser['Purchaser']['id']))?></li>
<?php } ?>
</ul>
