<button id="search_form_show_contact_people">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['ContactPersonSearch']) ){
		$hide = '';
	}
?>
<div id="search_form_contact_people"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'contact_people', 'action' => 'index');
	}?>
	<?php echo $form->create('ContactPerson', array('url' => $url)); ?>
	<table class="left_heading">
		<tr>
			<td colspan="6">Odběratel</td>
		</tr>
		<tr>
			<th>Název</th>
			<td><?php echo $form->input('ContactPersonSearch.Purchaser.name', array('label' => false))?></td>
			<th>Ulice</th>
			<td><?php echo $form->input('ContactPersonSearch.PurchaserAddress.street', array('label' => false))?></td>
			<th>Město</th>
			<td><?php echo $form->input('ContactPersonSearch.PurchaserAddress.city', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Obchodník</th>
			<td colspan="5"><?php echo $form->input('ContactPersonSearch.Purchaser.user_id', array('label' => false, 'options' => $users, 'empty' => true))?></td>
		</tr>
		<tr>
			<td colspan="6">Kontaktní osoba</td>
		</tr>
		<tr>
			<th>Jméno</th>
			<td><?php echo $form->input('ContactPersonSearch.ContactPerson.first_name', array('label' => false))?></td>
			<th>Příjmení</th>
			<td colspan="3"><?php echo $form->input('ContactPersonSearch.ContactPerson.last_name', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Ulice</th>
			<td><?php echo $form->input('ContactPersonSearch.ContactPersonAddress.street', array('label' => false))?></td>
			<th>Město</th>
			<td colspan="3"><?php echo $form->input('ContactPersonSearch.ContactPersonAddress.city', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
					$reset_url = $_SERVER['REQUEST_URI'];
					if ($this->params['action'] == 'user_index' && empty($this->params['named'])) {
						$reset_url = $_SERVER['REQUEST_URI'] . '/index';
					}
					$reset_url .= '/reset:contact_people';
					echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>
	<?php
		echo $form->hidden('ContactPersonSearch.ContactPerson.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_contact_people").click(function () {
		if ($('#search_form_contact_people').css('display') == "none"){
			$("#search_form_contact_people").show("slow");
		} else {
			$("#search_form_contact_people").hide("slow");
		}
	});
</script>