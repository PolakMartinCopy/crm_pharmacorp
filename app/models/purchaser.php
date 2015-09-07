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
		)
	);
	
	var $virtualFields = array(
		'name' => 'TRIM(CONCAT(Purchaser.degree_before, " ", Purchaser.first_name, " ", Purchaser.last_name, " ", Purchaser.degree_after))'
	);
	
	var $full_title = 'TRIM(CONCAT(Purchaser.degree_before, " ", Purchaser.first_name, " ", Purchaser.last_name, " ", Purchaser.degree_after))';
	
	var $export_fields = array(
		array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
		array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
		array('field' => 'BusinessPartner.ico', 'position' => '["BusinessPartner"]["ico"]', 'alias' => 'BusinessPartner.ico'),
		array('field' => 'BusinessPartner.dic', 'position' => '["BusinessPartner"]["dic"]', 'alias' => 'BusinessPartner.dic'),
		array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
		array('field' => 'Purchaser.name', 'position' => '["Purchaser"]["name"]', 'alias' => 'Purchaser.name'),
		array('field' => 'Purchaser.email', 'position' => '["Purchaser"]["email"]', 'alias' => 'Purchaser.email'),
		array('field' => 'Purchaser.phone', 'position' => '["Purchaser"]["phone"]', 'alias' => 'Purchaser.phone'),
		array('field' => 'Address.name', 'position' => '["Address"]["name"]', 'alias' => 'Address.name'),
		array('field' => 'Address.person_first_name', 'position' => '["Address"]["person_first_name"]', 'alias' => 'Address.person_first_name'),
		array('field' => 'Address.person_last_name', 'position' => '["Address"]["person_last_name"]', 'alias' => 'Address.person_last_name'),
		array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
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