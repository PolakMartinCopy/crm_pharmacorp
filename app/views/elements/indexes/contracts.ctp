<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('ID', 'Contract.id')?></th>
		<th><?php echo $this->Paginator->sort('Datum zah.', 'Contract.begin_date')?></th>
		<th><?php echo $this->Paginator->sort('Datum uk.', 'Contract.end_date')?></th>
		<th><?php echo $this->Paginator->sort('Měsíc', 'Contract.month')?></th>
		<th><?php echo $this->Paginator->sort('Rok', 'Contract.year')?></th>
		<th><?php echo $this->Paginator->sort('Částka', 'Contract.amount')?></th>
		<th><?php echo $this->Paginator->sort('Osoba', 'Contract.contact_person_name')?></th>
		<th><?php echo $this->Paginator->sort('Zadal', 'User.last_name')?></th>
		<th><?php echo $this->Paginator->sort('Schváleno', 'Contract.confirmed')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php
	$odd = '';
	foreach ($contracts as $contract) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
	?>
	<tr<?php echo $odd?>>
		<td><?php echo $contract['Contract']['id']?></td>
		<td><?php echo $contract['Contract']['begin_date']?></td>
		<td><?php echo $contract['Contract']['end_date']?></td>
		<td><?php
			$months = months();
			echo $months[$contract['Contract']['month']];
		?></td>
		<td><?php echo $contract['Contract']['year']?></td>
		<td align="right"><?php echo $contract['Contract']['amount']?></td>
		<td><?php echo $this->Html->link($contract['Contract']['contact_person_name'], array('controller' => 'contact_people', 'action' => 'view', $contract['Contract']['contact_person_id']))?></td>
		<td><?php echo $contract['User']['last_name']?>
		<td><?php echo ($contract['Contract']['confirmed'] ? 'ano' : 'ne') ?></td>
		<td><?php
			$links = array();
			if (!$contract['Contract']['confirm_requirement'] && !$contract['Contract']['confirmed']) {
				$links[] = $this->Html->link('Upravit', array('controller' => 'contracts', 'action' => 'edit', $contract['Contract']['id'], 'back_link' => isset($back_link) ? $back_link : base64_encode($_SERVER['REQUEST_URI'])));
				$links[] = $this->Html->link('Smazat', array('controller' => 'contracts', 'action' => 'delete', $contract['Contract']['id'], 'back_link' => isset($back_link) ? $back_link : base64_encode($_SERVER['REQUEST_URI'])), array(), 'Opravdu chcete dohodu smazat?');
			}
			if (!$contract['Contract']['confirm_requirement'] && !$contract['Contract']['confirmed'] && isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Contracts/user_confirm_require')) {
				$links[] = $this->Html->link('Požádat o schválení', array('controller' => 'contracts', 'action' => 'confirm_require', $contract['Contract']['id'], 'back_link' => isset($back_link) ? $back_link : base64_encode($_SERVER['REQUEST_URI'])));
			}
			if ($contract['Contract']['confirm_requirement'] && !$contract['Contract']['confirmed'] && isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Contracts/user_confirm')) {
				$links[] = $this->Html->link('Schválit', array('controller' => 'contracts', 'action' => 'confirm', $contract['Contract']['id'], 'back_link' => isset($back_link) ? $back_link : base64_encode($_SERVER['REQUEST_URI'])));
			}
			if ($contract['Contract']['confirm_requirement'] && !$contract['Contract']['confirmed'] && isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Contracts/user_cancel_confirm_requirement')) {
				$links[] = $this->Html->link('Zrušit požadavek', array('controller' => 'contracts', 'action' => 'cancel_confirm_requirement', $contract['Contract']['id'], 'back_link' => isset($back_link) ? $back_link : base64_encode($_SERVER['REQUEST_URI'])));
			}
			echo implode('&nbsp;| ', $links);
		?></td>
	</tr>
	<?php } ?>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>