<h1>Přidat typ edukace</h1>
<ul>
	<li><?php echo $this->Html->link('Seznam typů edukací', array('controller' => 'education_types', 'action' => 'index'))?>
</ul>
<?php echo $this->Form->create('EducationType')?>
<table class="left_heading">
	<tr>
		<th>Název</th>
		<td><?php echo $this->Form->input('EducationType.name', array('label' => false))?></td>
	</tr>
</table>
<?php echo $this->Form->hidden('EducationType.active', array('value' => true))?>
<?php echo $this->Form->submit('Uložit')?>
<?php echo $this->Form->end()?>
<ul>
	<li><?php echo $this->Html->link('Seznam typů edukací', array('controller' => 'education_types', 'action' => 'index'))?>
</ul>