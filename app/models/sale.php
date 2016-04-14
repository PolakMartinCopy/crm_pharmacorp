<?php 
App::import('Model', 'Transaction');
class Sale extends Transaction {
	var $name = 'Sale';
	
	var $useTable = 'transactions';
	
	var $export_file = 'files/sales.csv';
	
	var $delivery_note_created = false;
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}
	
	function beforeFind($queryData) {
		$queryData['conditions']['Sale.transaction_type_id'] = 3;
		return $queryData;
	}
	
	function afterSave($created) {
		$data = $this->data;
		parent::afterSave($created);
		if ($created) {
			$wallet = 0;
			foreach ($data['ProductsTransaction'] as $pt) {
				$wallet += $pt['education'] * (-$pt['quantity']);
			}
			
			return $this->Purchaser->wallet_transaction($data['Sale']['purchaser_id'], $wallet);
		}
		return true;
	}
	
	function getPrice($id) {
		$sale = $this->find('first', array(
			'conditions' => array('Sale.id' => $id),
			'contain' => array('ProductsTransaction'),
		));
		$wallet = 0;
		foreach ($sale['ProductsTransaction'] as $pt) {
			$wallet += $pt['education'] * (-$pt['quantity']);
		}
		
		return $wallet;
	}
	
	function export_fields() {
		$export_fields = parent::export_fields();
		
		// u prodeju se chci zbavit zapornych hodnot u mnozstvi a celkove ceny
		$res = array();
		foreach ($export_fields as $export_field) {
			if ($export_field['alias'] == 'ProductsTransaction.quantity') {
				$res[] = array(
					'field' => 'ABS(`ProductsTransaction`.`quantity`) AS ProductsTransaction__abs_quantity',
					'position' => '["ProductsTransaction"]["abs_quantity"]',
					'alias' => 'ProductsTransaction.abs_quantity'
				);
			} elseif ($export_field['alias'] == 'ProductsTransaction.total_price') {
				$res[] = array(
					'field' => 'ABS(`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) AS ProductsTransaction__abs_total_price',
					'position' => '["ProductsTransaction"]["abs_total_price"]',
					'alias' => 'ProductsTransaction.abs_total_price'
				);
			} elseif ($export_field['alias'] == 'Transaction.margin') {
				$res[] = array(
					'field' => 'ABS(ROUND((`ProductsTransaction`.`product_margin` * `ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) / 100, 2)) AS Transaction__abs_margin',
					'position' => '["Transaction"]["abs_margin"]',
					'alias' => 'Transaction.abs_margin'
				);
			} else {
				$res[] = $export_field;
			}
		}
		return $res;
	}
}
?>
