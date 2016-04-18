<?php 
class Purchaser extends AppModel {
	var $name = 'Purchaser';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'User',
		'PurchaserType',
		'EducationType',
		'BusinessPartner'
	);
	
	var $hasOne = array(
		'Address' => array(
			'dependent' => true
		)
	);
	
	var $hasMany = array(
		'ContactPerson' => array(
			'dependent' => true
		),
		'BusinessSession',
		'Imposition',
		'Transaction',
		'Sale',
		'DeliveryNote',
		'StoreItem' => array(
			'dependent' => true
		),
		'Document' => array(
			'dependent' => true
		),
		'WalletTransaction'
	);
	
	var $validate = array(
		'business_partner_name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Vyberte obchodního partnera'
			)
		),
		'business_partner_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Vyberte obchodního partnera'
			)
		),
		'wallet_correction' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte hodnotu korekce'
			)
		)
	);
	
	var $virtualFields = array(
		'name' => 'TRIM(CONCAT(Purchaser.degree_before, " ", Purchaser.first_name, " ", Purchaser.last_name, " ", Purchaser.degree_after))'
	);
	
	var $full_title = 'TRIM(CONCAT(Purchaser.degree_before, " ", Purchaser.first_name, " ", Purchaser.last_name, " ", Purchaser.degree_after))';
	
	var $export_file = 'files/purchasers.csv';
	
	var $export_fields = array(
		array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
		array('field' => 'User.first_name', 'position' => '["User"]["first_name"]', 'alias' => 'User.first_name'),
		array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
		array('field' => 'BusinessPartner.ico', 'position' => '["BusinessPartner"]["ico"]', 'alias' => 'BusinessPartner.ico'),
		array('field' => 'Purchaser.name', 'position' => '["Purchaser"]["name"]', 'alias' => 'Purchaser.name'),
		array('field' => 'Purchaser.email', 'position' => '["Purchaser"]["email"]', 'alias' => 'Purchaser.email'),
		array('field' => 'Purchaser.phone', 'position' => '["Purchaser"]["phone"]', 'alias' => 'Purchaser.phone'),
		array('field' => 'Purchaser.wallet', 'position' => '["Purchaser"]["wallet"]', 'alias' => 'Purchaser.wallet'),
		array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
		array('field' => 'Address.number', 'position' => '["Address"]["number"]', 'alias' => 'Address.number'),
		array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
		array('field' => 'Address.zip', 'position' => '["Address"]["zip"]', 'alias' => 'Address.zip'),
		array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
	);
	
	function delete($id) {
		if ($this->hasAny(array('Purchaser.id' => $id))) {
			$save = array(
				'Purchaser' => array(
					'id' => $id,
					'active' => false
				)
			);
			return $this->save($save);
		}
		return false;
	}
	
	function wallet_transaction($id, $amount, $type = null, $opId = null, $userId = null) {
		$purchaser = $this->find('first', array(
			'conditions' => array('Purchaser.id' => $id),
			'contain' => array(),
			'fields' => array('Purchaser.id', 'Purchaser.wallet')
		));
	
		if (empty($purchaser)) {
			return false;
		}
		
		$wallet = $purchaser['Purchaser']['wallet'] + $amount;
		
		if ($res = $this->setWallet($id, $wallet) && isset($type) && isset($opId) && isset($userId)) {
			$walletTransactionSave = array(
				'WalletTransaction' => array(
					'purchaser_id' => $id,
					'amount' => $amount,
					'wallet_before' => $purchaser['Purchaser']['wallet'],
					'wallet_after' => $wallet,
					'user_id' => $userId,
					$type . '_id' => $opId
				)
			);
			$this->WalletTransaction->save($walletTransactionSave);
		}
		
		return $res;
	}
	
	function setWallet($id, $wallet) {
		$save = array(
			'Purchaser' => array(
				'id' => $id,
				'wallet' => $wallet
			)
		);
		
		return $this->save($save);		
	}
	
	function recountAllWallets() {
		$purchasers = $this->find('all', array(
//			'conditions' => array('Purchaser.active' => true),
			'contain' => array(),
			'fields' => array('Purchaser.id')
		));
		
		foreach ($purchasers as $purchaser) {
			$this->recountWallet($purchaser['Purchaser']['id']);
		}
		return true;
	}
	
	// prepocita stav penezenky odberatele
	function recountWallet($id = null) {
		if (!$id) {
			return false;
		}

		$wallet = 0;
		// prictu hodnoty poukazu
		$sales = $this->getSales($id);
//		debug($sales);
		foreach ($sales as $sale) {
			$wallet += $this->Sale->getPrice($sale['Sale']['id']);
		}
//		debug($wallet);
		// odectu hodnoty schvalenych dohod
		$contracts = $this->getContracts($id);
//		debug($contracts);
		foreach ($contracts as $contract) {
			$wallet -= $contract['Contract']['amount_vat'];
		}
//		debug($wallet);
		return $this->setWallet($id, $wallet);
	}
	
	function getSales($id) {
		$this->Sale->virtualFields = array();
		$sales = $this->Sale->find('all', array(
			'conditions' => array('Sale.purchaser_id' => $id),
			'contain' => array()
		));
		
		return $sales;
	}
	
	function getContracts($id, $onlyConfirmed = true) {
		$contracts = $this->find('all', array(
			'conditions' => array(
				'Purchaser.id' => $id,
				'Contract.confirmed' => true
			),
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'contact_people',
					'alias' => 'ContactPerson',
					'type' => 'INNER',
					'conditions' => array('ContactPerson.purchaser_id = Purchaser.id')
				),
				array(
					'table' => 'contracts',
					'alias' => 'Contract',
					'type' => 'INNER',
					'conditions' => array('Contract.contact_person_id = ContactPerson.id')
				)
			),
			'fields' => array('Contract.*')
		));
		return $contracts;
	}
	
	function do_form_search($conditions, $data){
		if ( !empty($data['BusinessPartner']['name']) ){
			$conditions[] = 'BusinessPartner.name LIKE \'%%' . $data['BusinessPartner']['name'] . '%%\'';
		}
		if ( !empty($data['BusinessPartner']['ico']) ){
			$conditions[] = 'BusinessPartner.ico LIKE \'%%' . $data['BusinessPartner']['ico'] . '%%\'';
		}
		if ( !empty($data['Purchaser']['last_name']) ){
			$conditions[] = 'Purchaser.last_name LIKE \'%%' . $data['Purchaser']['last_name'] . '%%\'';
		}
		if ( !empty($data['Purchaser']['icz']) ){
			$conditions[] = 'Purchaser.icz LIKE \'%%' . $data['Purchaser']['icz'] . '%%\'';
		}
		if ( !empty($data['Purchaser']['category']) ){
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
		if (!empty($data['Purchaser']['user_id'])) {
			$conditions['Purchaser.user_id'] = $data['Purchaser']['user_id'];
		}
	
		return $conditions;
	}
	
	function autocomplete_list($user, $term = null) {
		$conditions = array('Purchaser.active' => true);
		if ($user['User']['user_type_id'] == 3) {
			$conditions = array('Purchaser.user_id' => $user['User']['id']);
		}
		if ($term) {
			$conditions['Purchaser.name LIKE'] = '%' . $term . '%';
		}
		
		$purchasers = $this->find('all', array(
			'conditions' => $conditions,
			'order' => array('name' => 'asc'),
			'contain' => array(),
			'fields' => array('Purchaser.id', 'Purchaser.name')
		));
		
		$autocomplete_purchasers = array();
		foreach ($purchasers as $purchaser) {
			$autocomplete_purchasers[] = array(
				'label' => $this->autocomplete_field_info($purchaser['Purchaser']['id']),
				'value' => $purchaser['Purchaser']['id']
			);
		}
		return json_encode($autocomplete_purchasers);
	}
	
	function autocomplete_field_info($id) {
		$purchaser = $this->find('first', array(
			'conditions' => array('Purchaser.id' => $id),
			'contain' => array(
				'Address' => array(
					'fields' => array(
						'Address.id',
						'Address.street',
						'Address.number',
						'Address.city',
						'Address.zip',
					)
				)
			),
			'fields' => array(
				'Purchaser.id',
				'Purchaser.name',
			)
		));
		
		if (empty($purchaser)) {
			return false;
		}

		return $purchaser['Purchaser']['name'] . ', ' . $purchaser['Address']['street'] . ' ' . $purchaser['Address']['number'] . ', ' . $purchaser['Address']['city'] . ', ' . $purchaser['Address']['zip'];
	}
}
?>