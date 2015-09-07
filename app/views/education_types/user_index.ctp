<h1>Typy edukací</h1>
<ul>
	<li><?php echo $this->Html->link('Přidat typ edukace', array('controller' => 'education_types', 'action' => 'add'))?></li>
</ul>

<?php if (empty($education_types)) { ?>
<p><em>V systému nejsou žádné typy edukací.</em></p>
<?php } else { ?>
<table class="top_heading">
	<tr>
		<th><?php echo $this->Paginator->sort('ID', 'EducationType.id')?></th>
		<th><?php echo $this->Paginator->sort('Název', 'EducationType.name')?></th>
		<th>&nbsp;</th>
	</tr>
	<?php foreach ($education_types as $education_type) { ?>
	<tr>	
		<td><?php echo $education_type['EducationType']['id']?></td>
		<td><?php echo $education_type['EducationType']['name']?></td>
		<td><?php
			echo $this->Html->link('Upravit', array('action' => 'edit', $education_type['EducationType']['id'])) . ' | ';
			echo $this->Html->link('Smazat', array('action' => 'delete', $education_type['EducationType']['id']), array(), 'Opravdu chcete typ edukace ' . $education_type['EducationType']['name'] . ' odstranit?');
		?></td>
	</tr>
	<?php } ?>
</table>
<?php } ?>

<ul>
	<li><?php echo $this->Html->link('Přidat typ edukace', array('controller' => 'education_types', 'action' => 'add'))?></li>
</ul>