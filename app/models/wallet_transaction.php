<?php
class WalletTransaction extends AppModel {
	var $name = 'WalletTransaction';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'Purchaser',
		'User',
		'Sale',
		'Contract'
	);
}
