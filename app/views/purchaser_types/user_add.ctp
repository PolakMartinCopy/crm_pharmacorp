<h1>Přidat typ odběratele</h1>
<ul>
	<li><?php echo $this->Html->link('Seznam typů odběratelů', array('controller' => 'purchaser_types', 'action' => 'index'))?>
</ul>
<?php echo $this->Form->create('PurchaserType')?>
<table class="left_heading">
	<tr>
		<th>Název</th>
		<td><?php echo $this->Form->input('PurchaserType.name', array('label' => false))?></td>
	</tr>
</table>
<?php echo $this->Form->hidden('PurchaserType.active', array('value' => true))?>
<?php echo $this->Form->submit('Uložit')?>
<?php echo $this->Form->end()?>
<ul>
	<li><?php echo $this->Html->link('Seznam typů odběratelů', array('controller' => 'purchaser_types', 'action' => 'index'))?>
</ul>