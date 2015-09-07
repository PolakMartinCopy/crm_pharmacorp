<table class="top_heading">
	<tr>
		<th><?php echo $paginator->sort('ID', 'ContactPerson.id')?></th>
		<th><?php echo $paginator->sort('Křestní jméno', 'ContactPerson.first_name')?></th>
		<th><?php echo $paginator->sort('Příjmení', 'ContactPerson.last_name')?></th>
		<th><?php echo $paginator->sort('Telefon', 'ContactPerson.phone')?></th>
		<th><?php echo $paginator->sort('Mobilní telefon', 'ContactPerson.cellular')?></th>
		<th><?php echo $paginator->sort('Email', 'ContactPerson.email')?></th>
		<th><?php echo $paginator->sort('Odběratel', 'ContactPerson.purchaser_name')?></th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($contact_people as $contact_person) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $contact_person['ContactPerson']['id']?></td>
		<td><?php echo $contact_person['ContactPerson']['first_name']?></td>
		<td><?php echo $html->link($contact_person['ContactPerson']['last_name'], array('controller' => 'contact_people', 'action' => 'view', $contact_person['ContactPerson']['id']))?></td>
		<td><?php echo $contact_person['ContactPerson']['phone']?></td>
		<td><?php echo $contact_person['ContactPerson']['cellular']?></td>
		<td><?php echo $html->link($contact_person['ContactPerson']['email'], 'mailto:' . $contact_person['ContactPerson']['email'])?></td>
		<td><?php echo $html->link($contact_person['ContactPerson']['purchaser_name'], array('controller' => 'purchasers', 'action' => 'view', $contact_person['Purchaser']['id'], 'tab' => 7))?></td>
		<td class="actions"><?php
			$links = array();
			$links[] = $html->link('Upravit', array('controller' => 'contact_people', 'action' => 'edit', $contact_person['ContactPerson']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])));
			$links[] = $html->link('Zadat dohodu', array('controller' => 'contracts', 'action' => 'add', 'contact_person_id' => $contact_person['ContactPerson']['id'],	 'back_link' => $back_link));
			$links[] = $html->link('Smazat', array('controller' => 'contact_people', 'action' => 'delete', $contact_person['ContactPerson']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])), null, 'Opravdu chcete smazat kontatní osobu ' . $contact_person['ContactPerson']['first_name'] . ' ' . $contact_person['ContactPerson']['last_name'] . '?');
			echo implode('&nbsp| ', $links);
		?></td>
	</tr>
<?php } // end foreach ?>
</table>
<?php 
echo $paginator->numbers();
echo $paginator->prev('« Předchozí ', null, null, array('class' => 'disabled'));
echo $paginator->next(' Další »', null, null, array('class' => 'disabled'));
?>