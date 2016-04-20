<table class="top_heading">
	<tr>
		<th><?php echo $paginator->sort('ID', 'BusinessSession.id')?></th>
		<th><?php echo $paginator->sort('Datum', 'BusinessSession.date')?></th>
		<th><?php echo $paginator->sort('Odběratel', 'BusinessSession.purchaser_name')?></th>
		<th><?php echo $paginator->sort('Stav jednání', 'BusinessSessionState.name')?></th>
		<th><?php echo $paginator->sort('Popis', 'BusinessSession.short_desc')?></th>
		<th><?php echo $paginator->sort('Založil', 'User.last_name')?></th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($business_sessions as $business_session) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $business_session['BusinessSession']['id']?></td>
		<td><?php echo $business_session['BusinessSession']['date']?></td>
		<td><?php echo $html->link($business_session['BusinessSession']['purchaser_name'], array('controller' => 'purchasers', 'action' => 'view', $business_session['Purchaser']['id'], 'tab' => 8))?></td>
		<td><?php echo $business_session['BusinessSessionState']['name']?></td>
		<td><?php echo $business_session['BusinessSession']['short_desc']?></td>
		<td><?php echo $business_session['User']['last_name']?></td>
		<td class="actions"><?php 
			$links = array();
			$links[] = $html->link('Detail', array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])));
			// editovat, uzavrit a stornovat muzu pouze, pokud je uzivatel admin anebo je jednani otevreno
			if ($user['User']['user_type_id'] == 1 || $user['User']['user_type_id'] == 2 || $business_session['BusinessSession']['business_session_state_id'] == 1) {
				$links[] = $html->link('Upravit', array('controller' => 'business_sessions', 'action' => 'edit', $business_session['BusinessSession']['id'], 'back_link' => $back_link));
				$links[] = $html->link('Uzavřít', array('controller' => 'business_sessions', 'action' => 'close', $business_session['BusinessSession']['id'], 'back_link' => $back_link), null, 'Opravdu chcete obchodní jednání ' . $business_session['BusinessSession']['id'] . ' označit jako uzavřené?');
				$links[] = $html->link('Storno', array('controller' => 'business_sessions', 'action' => 'storno', $business_session['BusinessSession']['id'], 'back_link' => $back_link), null, 'Opravdu chcete obchodní jednání ' . $business_session['BusinessSession']['id'] . ' stornovat?');
			}
			if (($user['User']['user_type_id'] == 1 && $business_session['BusinessSession']['is_deletable']) {
				$links[] = $this->Html->link('Smazat', array('controller' => 'business_sessions', 'action' => 'delete', $business_session['BusinessSession']['id'], 'back_link' => $back_link), null, 'Opravdu chcete obchodní jednání ' . $business_session['BusinessSession']['id'] . ' smazat?');
			}
			echo implode('&nbsp| ', $links);
		?></td>
	</tr>
<?php } ?>
</table>
<?php
echo $paginator->numbers();
echo $paginator->prev('« Předchozí ', null, null, array('class' => 'disabled'));
echo $paginator->next(' Další »', null, null, array('class' => 'disabled'));
?>