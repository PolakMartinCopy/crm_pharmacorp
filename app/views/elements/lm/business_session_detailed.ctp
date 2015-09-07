<div class="menu_header">
	Obchodní jednání
</div>
<ul class="menu_links">
	<li><?php echo $html->link('Detail obchodního jednání', array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id']))?></li>
<?php if ($user['User']['user_type_id'] == 1 || $user['User']['user_type_id'] == 2 || $business_session['BusinessSession']['business_session_state_id'] == 1) { ?>
	<li><?php echo $html->link('Upravit obchodní jednání', array('controller' => 'business_sessions', 'action' => 'edit', $business_session['BusinessSession']['id'], 'back_link' => base64_encode(serialize($_SERVER['REQUEST_URI']))))?></li>
	<li><?php echo $html->link('Uzavřít obchodní jednání', array('controller' => 'business_sessions', 'action' => 'close', $business_session['BusinessSession']['id']))?></li>
	<li><?php echo $html->link('Stornovat obchodní jednání', array('controller' => 'business_sessions', 'action' => 'storno', $business_session['BusinessSession']['id']))?></li>
<?php } ?>
</ul>