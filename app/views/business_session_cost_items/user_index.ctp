<h1>Číselník nákladů</h1>

<button id="search_form_show_business_session_cost_items">vyhledávací formulář</button>
<?php
	$hide = ' style="display:none"';
	if ( isset($this->data['BusinessSessionCostItemSearch']) ){
		$hide = '';
	}
?>
<div id="search_form_business_session_cost_items"<?php echo $hide?>>
	<?php echo $form->create('BusinessSessionCostItem', array('url' => array('controller' => 'business_session_cost_items', 'action' => 'index'))); ?>
	<table class="left_heading">
		<tr>
			<th>Název</th>
			<td><?php echo $form->input('BusinessSessionCostItemSearch.BusinessSessionCostItem.name', array('label' => false))?></td>
		</tr>
		<tr>
			<td colspan="6">
				<?php echo $html->link('reset filtru', array('controller' => 'business_session_cost_items', 'action' => 'index', 'reset' => 'business_session_cost_items')) ?>
			</td>
		</tr>
	</table>
	<?php
		echo $form->hidden('BusinessSessionCostItemSearch.BusinessSessionCostItem.search_form', array('value' => 1));
		echo $form->submit('Vyhledávat');
		echo $form->end();
	?>
</div>

<script>
	$("#search_form_show_business_session_cost_items").click(function () {
		if ($('#search_form_business_session_cost_items').css('display') == "none"){
			$("#search_form_business_session_cost_items").show("slow");
		} else {
			$("#search_form_business_session_cost_items").hide("slow");
		}
	});
</script>

<?php
echo $form->create('CSV', array('url' => array('controller' => 'business_session_cost_items', 'action' => 'xls_export')));
echo $form->hidden('data', array('value' => serialize($find)));
echo $form->hidden('fields', array('value' => serialize($export_fields)));
echo $form->submit('CSV');
echo $form->end();

if (empty($items)) { ?>
<p><em>Číselník zboží je prázdný.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('Název', 'BusinessSessionCostItem.name')?></th>
		<th><?php echo $this->Paginator->sort('Cena', 'BusinessSessionCostItem.price')?></th>
		<th><?php echo $this->Paginator->sort('Množství', 'BusinessSessionCostItem.quantity')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php foreach ($items as $item) { ?>
	<tr>
		<td><?php echo $item['BusinessSessionCostItem']['name']?></td>
		<td align="right"><?php echo $item['BusinessSessionCostItem']['price']?></td>
		<td align="right"><?php echo $item['BusinessSessionCostItem']['quantity']?></td>
		<td><?php
			echo $this->Html->link('Upravit', array('action' => 'edit', $item['BusinessSessionCostItem']['id'])) . ' | ';
			echo $this->Html->link('Smazat', array('action' => 'delete', $item['BusinessSessionCostItem']['id']));
		?></td>
	</tr>
	<?php } ?>
</table>
<?php echo $this->Paginator->prev('« Předchozí', null, null, array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(); ?>
<?php echo $this->Paginator->next('Další »', null, null, array('class' => 'disabled')); ?>
<?php } ?>