<?php
class StoreItem extends AppModel {
	var $name = 'StoreItem';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'Purchaser',
		'Product'
	);
	
	var $virtualFields = array(
		'total_quantity' => 'SUM(StoreItem.quantity)',
		'total_price' => 'SUM(Product.price * StoreItem.quantity)',
		'item_total_price' => 'Product.price * StoreItem.quantity'
	);
	
	var $export_file = 'files/store_items.csv';
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->export_fields = array(
			array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
			array('field' => $this->Purchaser->virtualFields['name'], 'position' => '[0][\'' .  $this->Purchaser->virtualFields['name'] . '\']', 'alias' => 'Purchaser.name', 'escape_quotes' => false),
			array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
			array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
			array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
			array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
			array('field' => 'Product.id', 'position' => '["Product"]["id"]', 'alias' => 'Product.id'),
			array('field' => 'Product.vzp_code', 'position' => '["Product"]["vzp_code"]', 'alias' => 'Product.vzp_code'),
			array('field' => 'Product.name', 'position' => '["Product"]["name"]', 'alias' => 'Product.name'),
			array('field' => 'StoreItem.id', 'position' => '["StoreItem"]["id"]', 'alias' => 'StoreItem.id'),
			array('field' => 'StoreItem.quantity', 'position' => '["StoreItem"]["quantity"]', 'alias' => 'StoreItem.quantity'),
			array('field' => 'Unit.shortcut', 'position' => '["Unit"]["shortcut"]', 'alias' => 'Unit.shortcut'),
			array('field' => 'Product.price', 'position' => '["Product"]["price"]', 'alias' => 'Product.price'),
			array('field' => 'StoreItem.item_total_price', 'position' => '["StoreItem"]["item_total_price"]', 'alias' => 'StoreItem.item_total_price'),
			array('field' => 'Product.group_code', 'position' => '["Product"]["group_code"]', 'alias' => 'Product.group_code'),
		);
	}
	
/* 	function beforeSave() {
		if (isset($this->data['StoreItem']['id']) && $this->data['StoreItem']['quantity'] == 0) {
			$this->delete($this->data['StoreItem']['id']);
			return false;
		}
		return true;
	} */
	
	function do_form_search($conditions = array(), $data) {
		if (!empty($data['BusinessPartner']['name'])) {
			$conditions[] = 'BusinessPartner.name LIKE \'%%' . $data['BusinessPartner']['name'] . '%%\'';
		}
		if (!empty($data['BusinessPartner']['ico'])) {
			$conditions[] = 'BusinessPartner.ico LIKE \'%%' . $data['BusinessPartner']['ico'] . '%%\'';
		}
		if (!empty($data['Purchaser']['last_name'])) {
			$conditions[] = 'Purchaser.last_name LIKE \'%%' . $data['Purchaser']['last_name'] . '%%\'';
		}		
		if (!empty($data['Purchaser']['icz'])) {
			$conditions[] = 'Purchaser.icz LIKE \'%%' . $data['Purchaser']['icz'] . '%%\'';
		}
		if (!empty($data['Purchaser']['category'])) {
			$conditions[] = 'Purchaser.category LIKE \'%%' . $data['Purchaser']['category'] . '%%\'';
		}
		if ( !empty($data['Address']['city']) ){
			$conditions[] = 'Address.city LIKE \'%%' . $data['Address']['city'] . '%%\'';
		}
		if ( !empty($data['Address']['zip']) ){
			$conditions[] = 'Address.zip LIKE \'%%' . $data['Address']['zip'] . '%%\'';
		}
		if ( !empty($data['Address']['region']) ){
			$conditions[] = 'Address.region LIKE \'%%' . $data['Address']['region'] . '%%\'';
		}
		if (!empty($data['Product']['vzp_code'])) {
			$conditions[] = 'Product.vzp_code LIKE \'%%' . $data['Product']['vzp_code'] . '%%\'';
		}
		if (!empty($data['Product']['name'])) {
			$conditions[] = 'Product.name LIKE \'%%' . $data['Product']['name'] . '%%\'';
		}
		if (!empty($data['Product']['group_code'])) {
			$conditions[] = 'Product.group_code LIKE \'%%' . $data['Product']['group_code'] . '%%\'';
		}
	
		return $conditions;
	}
	
	// vrati pocet tydnu, jak dlouho mu vystaci stavajici pocet daneho produktu (podle toho, jak v minulosti dany produkt prodaval)
	function getWeekReserve($id = null) {
		if (!$id) {
			return false;
		}
		$virtualFields = $this->virtualFields;
		$this->virtualFields = array();
		$storeItem = $this->find('first', array(
			'conditions' => array('StoreItem.id' => $id),
			'contain' => array(),
		));
		$this->virtualFields = $virtualFields;
		if (empty($storeItem)) {
			return false;
		}
		
		$defaultStartDate = '2016-01-01';
		$startDate = date('Y-m-d', strtotime('-1 year'));
		if ($startDate < $defaultStartDate) {
			$startDate = $defaultStartDate;
		}
		$endDate = date('Y-m-d');
		$weeksDiff = datediff('ww', $startDate, $endDate);
		$weekReserveField = 'ABS(ROUND(' . $storeItem['StoreItem']['quantity'] . ' / (SUM(ProductsTransaction.quantity) / ' . $weeksDiff . ')))';

		$productsTransactionVirtualFields = $this->Purchaser->Sale->ProductsTransaction->virtualFields;
		$deliveryNoteVirtualFields = $this->Purchaser->Sale->virtualFields;
		
		$this->Purchaser->Sale->ProductsTransaction->virtualFields = array('reserve' => $weekReserveField);
		$this->Purchaser->Sale->virtualFields = array();
		// kolik techto produktu prodal odberatel v intervalu od pocatku do ted
		$product = $this->Purchaser->Sale->ProductsTransaction->find('first', array(
			'conditions' => array(
				'ProductsTransaction.product_id' => $storeItem['StoreItem']['product_id'],
				'Sale.date >=' => $startDate,
				'Sale.date <' => $endDate,
				'Sale.purchaser_id' => $storeItem['StoreItem']['purchaser_id'],
				'Sale.transaction_type_id' => 3
			),
			'contain' => array('Sale'),
			'group' => array('ProductsTransaction.product_id'),
			'fields' => array('ProductsTransaction.product_id', 'ProductsTransaction.reserve')
		));
		$this->Purchaser->Sale->ProductsTransaction->virtualFields = $productsTransactionVirtualFields;
		$this->Purchaser->Sale->virtualFields = $deliveryNoteVirtualFields;
		if (empty($product)) {
			return false;
		}
		return $product['ProductsTransaction']['reserve'];
	}
}
