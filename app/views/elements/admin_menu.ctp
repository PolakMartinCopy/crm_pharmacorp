<?php
	if ( !isset($active_tab) ){
		$active_tab = '';
	}
?>

<ul id="top_nav">
<?php if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/BusinessPartners/user_index')) { ?>
	<li><?php echo $html->link('Obch. partneři', array('controller' => 'business_partners', 'action' => 'index'), array('class' => ($active_tab == 'business_partners' ? 'active' : '')))?></li>
<?php } ?>
<?php if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Purchasers/user_index')) { ?>
	<li><?php echo $html->link('Odběratelé', array('controller' => 'purchasers', 'action' => 'index'), array('class' => ($active_tab == 'purchasers' ? 'active' : '')))?>
<?php }
	if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/BusinessSessions/user_index')) { ?>
	<li><?php echo $html->link('Obch. jednání', array('controller' => 'business_sessions', 'action' => 'index'), array('class' => ($active_tab == 'business_sessions' ? 'active' : '')))?></li>
<?php }
	if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/ContactPeople/user_index')) { ?>
	<li><?php echo $html->link('Kont. osoby', array('controller' => 'contact_people', 'action' => 'index'), array('class' => ($active_tab == 'contact_people' ? 'active' : '')))?></li>
<?php }?>
	<li><?php echo $html->link('Úkoly', array('controller' => 'impositions', 'action' => 'index'), array('class' => ($active_tab == 'impositions' ? 'active' : '')))?></li>
	<li><?php echo $this->Html->link('Dod. listy', array('controller' => 'delivery_notes', 'action' => 'index'), array('class' => ($active_tab == 'delivery_notes' ? 'active' : '')))?></li>
	<li><?php echo $this->Html->link('Poukazy', array('controller' => 'sales', 'action' => 'index'), array('class' => ($active_tab == 'sales' ? 'active' : '')))?></li>
	<li><?php echo $this->Html->link('Sklady', array('controller' => 'store_items', 'action' => 'index'), array('class' => ($active_tab == 'store_items' ? 'active' : '')))?></li>
	<li><?php echo $this->Html->link('Pohyby', array('controller' => 'transactions', 'action' => 'index'), array('class' => ($active_tab == 'transactions' ? 'active' : '')))?></li>
	<li><?php echo $this->Html->link('Dohody', array('controller' => 'contracts', 'action' => 'index'), array('class' => ($active_tab == 'contracts' ? 'active' : '')))?></li>
<?php if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/users/index')) { ?>
		<li><?php echo $html->link('Uživatelé', array('controller' => 'users', 'action' => 'index'), array('class' => ($active_tab == 'users' ? 'active' : '')))?></li>
<?php } else { ?>
		<li><?php echo $html->link('Uživatelé', array('controller' => 'users', 'action' => 'edit', $logged_in_user['User']['id']), array('class' => ($active_tab == 'users' ? 'active' : '')))?></li>
<?php } ?>
<?php if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/products/index')) { ?>
	<li><?php echo $this->Html->link('Zboží', array('controller' => 'products', 'action' => 'index'), array('class' => ($active_tab == 'products' ? 'active' : '')))?></li>
<?php } ?>
<?php if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/users/user_setting')) { ?>
		<li><?php echo $html->link('Nastavení', array('controller' => 'anniversary_types', 'action' => 'index'), array('class' => ($active_tab == 'settings' ? 'active' : '')))?></li>
<?php } ?>
	<li><?php echo $html->link('Odhlásit', array('controller' => 'users', 'action' => 'logout'))?></li>
</ul><div class="clearer"></div>