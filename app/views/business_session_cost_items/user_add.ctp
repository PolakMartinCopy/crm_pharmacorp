<h1>Nový náklad</h1>
<?php echo $this->Form->create('BusinessSessionCostItem')?>
<table class="left_heading">
	<tr>
		<th>Název</th>
		<td><?php echo $this->Form->input('BusinessSessionCostItem.name', array('label' => false, 'size' => 70))?></td>
	</tr>
	<tr>
		<th>Cena</th>
		<td><?php echo $this->Form->input('BusinessSessionCostItem.price', array('label' => false, 'size' => 7, 'after' => '&nbsp;Kč'))?></td>
	</tr>
	<tr>
		<th>Množství</th>
		<td><?php echo $this->Form->input('BusinessSessionCostItem.quantity', array('label' => false, 'size' => 5))?></td>
	</tr>
</table>
<?php echo $this->Form->hidden('BusinessSessionCostItem.active', array('value' => true))?>
<?php echo $this->Form->submit('Uložit')?>
<?php echo $this->Form->end()?>