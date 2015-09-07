<h1>Přidat typ nákladů</h1>
<ul>
	<li><?php echo $html->link('Zpět na seznam typů nákladů', array('controller' => 'cost_types', 'action' => 'index'))?></li>
</ul>

<?php echo $form->create('CostType', array('url' => array('controller' => 'cost_types', 'action' => 'add')))?>
<table class="left_heading">
	<tr>
		<th>Název</th>
		<td><?php echo $form->input('CostType.name', array('label' => false))?></td>
	</tr>
</table>
<?php echo $form->submit('Uložit')?>
<?php echo $form->end() ?>

<ul>
	<li><?php echo $html->link('Zpět na seznam typů nákladů', array('controller' => 'cost_types', 'action' => 'index'))?></li>
</ul>