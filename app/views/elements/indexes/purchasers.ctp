<table class="top_heading" style="width:100%">
	<tr>
		<th><?php echo $this->Paginator->sort('ID', 'Purchaser.id')?></th>
		<th><?php echo $this->Paginator->sort('Odběratel', 'Purchaser.name')?></th>
		<th><?php echo $this->Paginator->sort('Obchodní partner', 'BusinessPartner.name')?>
		<th><?php echo $this->Paginator->sort('Email', 'Purchaser.email')?></th>
		<th><?php echo $this->Paginator->sort('Telefon', 'Purchaser.phone')?></th>
		<th><?php echo $this->Paginator->sort('Účet', 'Purchaser.wallet')?></th>
		<th><?php echo $this->Paginator->sort('IČZ', 'Purchaser.icz')?></th>
		<th>&nbsp;</th>
	</tr>
<?php
	$odd = '';
	foreach ($purchasers as $purchaser) {
		$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
?>
	<tr<?php echo $odd?>>
		<td><?php echo $purchaser['Purchaser']['id']?></td>
		<td><?php echo $html->link($purchaser['Purchaser']['name'], array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id']))?></td>
		<td><?php echo $this->Html->link($purchaser['BusinessPartner']['name'], array('controller' => 'business_partners', 'action' => 'view', $purchaser['BusinessPartner']['id']))?></td>
		<td><?php echo $purchaser['Purchaser']['email']?></td>
		<td><?php echo $purchaser['Purchaser']['phone']?></td>
		<td align="right"><?php echo $purchaser['Purchaser']['wallet']?></td>
		<td><?php echo $purchaser['Purchaser']['icz']?></td>
		<td class="actions"><?php 
			$back_link = (isset($business_partner['BusinessPartner']['id']) ? array('controller' => 'business_partners', 'action' => 'view', $business_partner['BusinessPartner']['id'], 'tab' => 3) : array('controller' => 'purchasers', 'action' => 'index'));
			$back_link = serialize($back_link);
			$back_link = base64_encode($back_link);
			
			$links = array();
			$links[] = $html->link('Detail', array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id']));
			$links[] = $html->link('Upravit', array('controller' => 'purchasers', 'action' => 'edit', $purchaser['Purchaser']['id'], 'back_link' => $back_link));
			if (isset($acl) && $acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Purchasers/user_delete')) {
				$links[] = $html->link('Smazat', array('controller' => 'purchasers', 'action' => 'delete', $purchaser['Purchaser']['id'], 'back_link' => $back_link), null, 'Opravdu chcete smazat odběratele se vším, co k němu náleží?');
			}
			
			echo implode('&nbsp;| ', $links)
		?></td>
	</tr>
<?php } // end foreach?>
</table>