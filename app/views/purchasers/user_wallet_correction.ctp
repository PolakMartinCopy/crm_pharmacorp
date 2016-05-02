<h1>Korekce peněženky odběratele</h1>
<ul>
	<li><?php echo $this->Html->link('Detail odběratele', array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id']))?></li>
</ul>

<?php echo $this->Form->create('Purchaser')?>
<table class="left_heading">
	<tr>
		<th>Odběratel</th>
		<td><?php echo $purchaser['Purchaser']['name']?></td>
	</tr>
	<tr>
		<th>Stav účtu</th>
		<th><?php echo $purchaser['Purchaser']['wallet']?>&nbsp;Kč</th>
	</tr>
	<tr>
		<th>Hodnota korekce</th>
		<td><?php echo $this->Form->input('Purchaser.wallet_correction', array('label' => false, 'style' => 'text-align:right', 'div' => false, 'after' => '&nbsp;Kč'))?></td>
	</tr>
	<tr>
		<th>Datum</th>
		<td><?php echo $this->Form->input('WalletTransaction.0.date', array('label' => false, 'type' => 'text'))?></td>
	</tr>
	<tr>
		<th>Komentář</th>
		<td><?php echo $this->Form->input('WalletTransaction.0.comment', array('label' => false, 'cols' => 70, 'rows' => 5))?></td>
	</tr>
</table>
<?php echo $this->Form->hidden('Purchaser.id')?>
<?php echo $this->Form->submit('Uložit')?>
<?php echo $this->Form->end()?>

<script type="text/javascript">
var dates = $( "#WalletTransaction0Date" ).datepicker({
	defaultDate: "+1w",
	changeMonth: false,
	numberOfMonths: 1,
});
</script>