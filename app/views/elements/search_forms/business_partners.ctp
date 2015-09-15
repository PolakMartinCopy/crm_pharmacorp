<button id="search_form_show">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['BusinessPartner']) ){
		$hide = '';
	}
?>
<div id="search_form"<?php echo $hide?>>
	<?php if (!isset($url)) {
		$url = array('controller' => 'business_partners', 'action' => 'index');
	}?>
	<?php echo $form->create('BusinessPartner', array('url' => $url))?>
	<table class="left_heading">
		<tr>
			<td colspan="6">Obchodní partner</td>
		</tr>
		<tr>
			<th>Název</th>
			<td><?php echo $form->input('BusinessPartner.name', array('label' => false))?></td>
			<th>Příjmení</th>
			<td><?php echo $form->input('BusinessPartner.last_name', array('label' => false))?></td>
			<th>IČO</th>
			<td><?php echo $form->input('BusinessPartner.ico', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Město</th>
			<td><?php echo $form->input('Address.city', array('label' => false))?></td>
			<th>PSČ</th>
			<td><?php echo $form->input('Address.zip', array('label' => false))?></td>
			<th>Okres</th>
			<td><?php echo $form->input('Address.region', array('label' => false))?></td>
		</tr>
		<tr>
			<th>Obchodník</th>
			<td colspan="5"><?php echo $form->input('Purchaser.user_id', array('label' => false, 'options' => $users, 'empty' => true))?></td>
		</tr>
		<tr>
			<td colspan="6"><?php
				$reset_url = $url;
				$reset_url['reset'] = 'business_partners';
				echo $html->link('reset filtru', $reset_url);
			?></td>
		</tr>
	</table>
	
	<?php
		echo $form->hidden('BusinessPartner.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
	
</div>

<script>
	$("#search_form_show").click(function () {
		if ($('#search_form').css('display') == "none"){
			$("#search_form").show("slow");
		} else {
			$("#search_form").hide("slow");
		}
	});
</script>