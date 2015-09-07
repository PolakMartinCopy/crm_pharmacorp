<?php
class Transaction extends AppModel {
	var $name = 'Transaction';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'Purchaser',
		'TransactionType',
		'User'
	);
	
	var $hasMany = array(
		'ProductsTransaction' => array(
			'dependent' => true
		)	
	);
	
	var $validate = array(
		'purchaser_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Vyberte odběratele'
			)
		),
		'user_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Není zadán uživatel, který vkládá transakci'
			)
		),
		'date' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte datum'
			)
		)
	);
	
	var $virtualFields = array(
		'quantity' => '`ProductsTransaction`.`quantity`',
		'abs_quantity' => 'ABS(`ProductsTransaction`.`quantity`)',
		'total_price' => '`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`',
		'abs_total_price' => 'ABS(`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`)',
		'margin' => 'ROUND((`ProductsTransaction`.`product_margin` * `ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) / 100, 2)',
		'abs_margin' => 'ABS(ROUND((`ProductsTransaction`.`product_margin` * `ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) / 100, 2))'
	);
	
	var $export_file = 'files/transactions.csv';
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['code'] = 'CONCAT(TransactionType.document_prefix, ' . $this->alias . '.year, ' . $this->alias . '.month, ' . $this->alias . '.order)';
	}

	function afterFind($results, $primary) {
		if ($this->alias == 'Transaction') {
			foreach ($results as &$result) {
				if (isset($result['TransactionType']['subtract']) && $result['TransactionType']['subtract']) {
					if (isset($result['ProductsTransaction']['quantity'])) {
						$result['ProductsTransaction']['quantity'] = -$result['ProductsTransaction']['quantity'];
					}
					if (isset($result['ProductsTransaction']['total_price'])) {
						$result['ProductsTransaction']['total_price'] = -$result['ProductsTransaction']['total_price'];
					}
				}
			}
		}
		return $results;
	}
	
	function beforeSave() {
		$this->data[$this->alias]['date'] = date('Y-m-d');

		// odnastavim id odberatele, pokud nemam nastaveno jmeno odberatele
		if (isset($this->data[$this->alias]['purchaser_id']) && isset($this->data[$this->alias]['purchaser_name']) && empty($this->data[$this->alias]['purchaser_name'])) {
			$this->data[$this->alias]['purchaser_id'] = null;
		}

		return true;
	}
	
	function afterSave($created) {
		if ($created) {
			$transaction_type_id = 1;
			if ($this->alias == 'Sale') {
				$transaction_type_id = 3;
			}
			
			$year = date('Y');
			$month = date('m');
			
			// vygeneruju cislo dokladu
			$transaction = array(
				$this->alias => array(
					'id' => $this->id,
					'year' => $year,
					'month' => $month,
					'order' => $this->docNumber($transaction_type_id, $year, $month)
				)
			);

			$this->create();
			$this->save($transaction);
		}
		
		return true;
	}
	
	function afterDelete() {
		// smazu taky pdf soubor z disku
		if (file_exists(DL_FOLDER . $this->id . '.pdf')) {
			return unlink(DL_FOLDER . $this->id . '.pdf');
		}
		
		return true;
	}
	
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
		if (!empty($data[$this->alias]['date_from'])) {
			$date_from = explode('.', $data[$this->alias]['date_from']);
			$date_from = $date_from[2] . '-' . $date_from[1] . '-' . $date_from[0];
			$conditions['DATE(' . $this->alias . '.created) >='] = $date_from;
		}
		if (!empty($data[$this->alias]['date_to'])) {
			$date_to = explode('.', $data[$this->alias]['date_to']);
			$date_to = $date_to[2] . '-' . $date_to[1] . '-' . $date_to[0];
			$conditions['DATE(' . $this->alias . '.created) <='] = $date_to;
		}
		if (!empty($data[$this->alias]['code'])) {
			$conditions[] = $this->virtualFields['code'] . ' LIKE \'%%' . $data[$this->alias]['code'] . '%%\'';
		}
		if (!empty($data[$this->alias]['user_id'])) {
			$conditions[$this->alias . '.user_id'] = $data[$this->alias]['user_id'];
		}
		if (!empty($data['Product']['name'])) {
			$conditions[] = 'Product.name LIKE \'%%' . $data['Product']['name'] . '%%\'';
		}
		if (!empty($data['Product']['group_code'])) {
			$conditions[] = 'Product.group_code LIKE \'%%' . $data['Product']['group_code'] . '%%\'';
		}
		if (!empty($data['Product']['vzp_code'])) {
			$conditions[] = 'Product.vzp_code LIKE \'%%' . $data['Product']['vzp_code'] . '%%\'';
		}

		return $conditions;
	}
	
	function export_fields() {
		// TODO - doplnit atributy, ktere chteji v CSV exportu
		$export_fields = array(
			array('field' => 'ProductsTransaction.id', 'position' => '["ProductsTransaction"]["id"]', 'alias' => 'ProductsTransaction.id'),
			array('field' => $this->alias . '.date', 'position' => '["' . $this->alias . '"]["date"]', 'alias' => $this->alias . '.date'),
			array('field' => $this->alias . '.code', 'position' => '["' . $this->alias . '"]["code"]', 'alias' => $this->alias . '.code'),
			array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
			array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
			array('field' => 'BusinessPartner.ico', 'position' => '["BusinessPartner"]["ico"]', 'alias' => 'BusinessPartner.ico'),
			array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
			array('field' => $this->Purchaser->virtualFields['name'], 'position' => '[0][\'' .  $this->Purchaser->virtualFields['name'] . '\']', 'alias' => 'Purchaser.name', 'escape_quotes' => false),
			array('field' => 'Product.id', 'position' => '["Product"]["id"]', 'alias' => 'Product.id'),
			array('field' => 'Product.name', 'position' => '["Product"]["name"]', 'alias' => 'Product.name'),
			array('field' => 'Product.vzp_code', 'position' => '["Product"]["vzp_code"]', 'alias' => 'Product.vzp_code'),
			array('field' => 'Product.group_code', 'position' => '["Product"]["group_code"]', 'alias' => 'Product.group_code'),
			array('field' => 'ProductsTransaction.quantity', 'position' => '["ProductsTransaction"]["quantity"]', 'alias' => 'ProductsTransaction.quantity'),
			array('field' => 'ProductsTransaction.unit_price', 'position' => '["ProductsTransaction"]["unit_price"]', 'alias' => 'ProductsTransaction.unit_price'),
			array('field' => '`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity` AS `ProductsTransaction__total_price`', 'position' => '["ProductsTransaction"]["total_price"]', 'alias' => 'ProductsTransaction.total_price'),
			array('field' => 'Unit.shortcut', 'position' => '["Unit"]["shortcut"]', 'alias' => 'Unit.shortcut'),
			array('field' => 'User.id', 'position' => '["User"]["id"]', 'alias' => 'User.id'),
			array('field' => 'TransactionType.id', 'position' => '["TransactionType"]["id"]', 'alias' => 'TransactionType.id'),
			array('field' => 'TransactionType.name', 'position' => '["TransactionType"]["name"]', 'alias' => 'TransactionType.name'),
			array('field' => 'User.last_name', 'position' => '["User"]["last_name"]', 'alias' => 'User.last_name')
		);
		
		return $export_fields;
	}
	
	function docNumber($transaction_type_id, $year = null, $month = null) {
		if (!$month) {
			$month = date('m');
		}
		if (!$year) {
			$year = date('Y');
		}
		
		$conditions = array(
			'transaction_type_id' => $transaction_type_id,
			'year' => $year,
			'month' => $month
		);

		// musim ziskat cislo daneho typu dokladu v tomto mesici a roce
		$last = $this->find('first', array(
			'conditions' => $conditions,
			'contain' => array(),
			'fields' => array('order'),
			'order' => array('created' => 'desc')
		));
		
		if (empty($last)) {
			return '001';
		}
		$order = $last[$this->alias]['order'];
		$order += 1;
		if (strlen($order) == 1) {
			$order = '00' . $order;
		} elseif (strlen($order) == 2) {
			$order = '0' . $order;
		}
		return $order;
	}
	
	function docNumberById($id) {
		$virtualFields = $this->virtualFields;
		$this->virtualFields = array(
			'code' => $virtualFields['code']
		);
		$alias = $this->alias;
		$transaction = $this->find('first', array(
			'conditions' => array($alias . '.id' => $id),
			'contain' => array('TransactionType'),
			'fields' => array($alias . '.code')
		));
		
		$this->virtualFields = $virtualFields;
		
		if (empty($transaction)) {
			return false;
		}
		return $transaction[$alias]['code'];
	}
}
