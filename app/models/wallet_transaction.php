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
	
	var $export_file = 'files/wallet_transactions.csv';
	
	var $export_fields = array(
		array('field' => 'WalletTransaction.id', 'position' => '["WalletTransaction"]["id"]', 'alias' => 'WalletTransaction.id'),
		array('field' => 'WalletTransaction.created', 'position' => '["WalletTransaction"]["created"]', 'alias' => 'WalletTransaction.created'),
		array('field' => 'WalletTransaction.amount', 'position' => '["WalletTransaction"]["amount"]', 'alias' => 'WalletTransaction.amount'),
		array('field' => 'WalletTransaction.wallet_before', 'position' => '["WalletTransaction"]["wallet_before"]', 'alias' => 'WalletTransaction.wallet_before'),
		array('field' => 'WalletTransaction.wallet_after', 'position' => '["WalletTransaction"]["wallet_after"]', 'alias' => 'WalletTransaction.wallet_after'),
		array('field' => 'WalletTransaction.user_name', 'position' => '["WalletTransaction"]["user_name"]', 'alias' => 'WalletTransaction.user_name'),
		array('field' => 'WalletTransaction.date', 'position' => '["WalletTransaction"]["date"]', 'alias' => 'WalletTransaction.date'),
		array('field' => 'WalletTransaction.comment', 'position' => '["WalletTransaction"]["comment"]', 'alias' => 'WalletTransaction.comment'),
	);
}
