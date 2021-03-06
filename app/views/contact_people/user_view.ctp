<h1><?php echo $contact_person['ContactPerson']['salutation']?></h1>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Info</a></li>
		<li><a href="#tabs-2">Výročí</a></li>
	</ul>
	
<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-1">
		<h2>Základní informace</h2>
		<!-- detaily o kontaktni osobe -->
		<table class="left_heading">
			<tr>
				<th>ID</th>
				<td><?php echo $contact_person['ContactPerson']['id']?></td>
			</tr>
			<tr>
				<th>Kontakní osoba</th>
				<td><?php echo $contact_person['ContactPerson']['name']?></td>
			</tr>
			<tr>
				<th>Odběratel</th>
				<td><?php echo $html->link($contact_person['Purchaser']['name'], array('controller' => 'purchasers', 'action' => 'view', $contact_person['Purchaser']['id'], 'tab' => 7))?></td>
			</tr>
			<tr>
				<th>Telefon</th>
				<td><?php echo $contact_person['ContactPerson']['phone']?></td>
			</tr>
			<tr>
				<th>Mobilní telefon</th>
				<td><?php echo $contact_person['ContactPerson']['cellular']?></td>
			</tr>
			<tr>
				<th>Email</th>
				<td><?php echo $html->link($contact_person['ContactPerson']['email'], 'mailto:' . $contact_person['ContactPerson']['email'])?></td>
			</tr>
			<tr>
				<th>Číslo bank. účtu</th>
				<td><?php echo $contact_person['ContactPerson']['bank_account']?></td>
			</tr>
			<tr>
				<th>Datum narození</th>
				<td><?php echo db2cal_date($contact_person['ContactPerson']['birthday'])?></td>
			</tr>
			<tr>
				<th>Rodné číslo</th>
				<td><?php echo $contact_person['ContactPerson']['birth_certificate_number']?></td>
			</tr>
			<tr>
				<th>Poznámka</th>
				<td><?php echo $contact_person['ContactPerson']['note']?></td>
			</tr>
			<tr>
				<th>Koníčky</th>
				<td><?php echo $contact_person['ContactPerson']['hobby']?></td>
			</tr>
			<tr>
				<th>Aktivní</th>
				<td><?php echo $contact_person['ContactPerson']['active']?></td>
			</tr>
		</table>
		
		<ul>
			<li><?php echo $html->link('Upravit kontaktní osobu', array('controller' => 'contact_people', 'action' => 'edit', $contact_person['ContactPerson']['id']))?></li>
		</ul>
	</div>
	
<?php /* TAB 2 ****************************************************************************************************************/ ?>
	<div id="tabs-2">
		<!-- funkce k vyrocim -->
		<h2>Výročí</h2>
		<ul>
			<li><?php echo $html->link('Přidat výročí', array('controller' => 'anniversaries', 'action' => 'add', 'contact_person_id' => $contact_person['ContactPerson']['id']))?></li>
		</ul>
		<?php if (empty($anniversaries)) { ?>
		<p><em>Kontaktní osoba nemá žádná výročí.</em></p>
		<?php } else { ?>
		<table class="top_heading">
			<tr>
				<th>ID</th>
				<th>Typ</th>
				<th>Datum</th>
				<th>Akce</th>
				<th>&nbsp;</th>
			</tr>
		<?php
			$odd = '';
			foreach ($anniversaries as $anniversary) {
				$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
		?>
			<tr<?php echo $odd?>>
				<td><?php echo $anniversary['Anniversary']['id']?></td>
				<td><?php echo $anniversary['AnniversaryType']['name']?></td>
				<td><?php echo $anniversary['Anniversary']['date']?></td>
				<td><?php echo $anniversary['AnniversaryAction']['name']?></td>
				<td class="actions">
					<?php echo $html->link('Upravit', array('controller' => 'anniversaries', 'action' => 'edit', $anniversary['Anniversary']['id']))?>
					<?php echo $html->link('Smazat', array('controller' => 'anniversaries', 'action' => 'delete', $anniversary['Anniversary']['id']), null, 'Opravdu chcete zvolené výročí smazat?')?>
				</td>
			</tr>
		<?php } // end foreach?>
		</table>
		<?php } // end if?>
	</div>
</div>