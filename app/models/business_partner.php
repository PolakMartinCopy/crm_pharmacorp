<?php
class BusinessPartner extends AppModel {
	var $name = 'BusinessPartner';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array(
		'Address' => array(
			'dependent' => true
		),
		'BusinessPartnerNote' => array(
			'dependent' => true	
		),
		'Purchaser'
	);
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název obchodního partnera'
			)
		),
		'ico' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte IČ obchodního partnera'
			),
			'isUnique' => array(
				'rule' => 'isUnique',
				'message' => 'Obchodní partner s daným IČem již v systému existuje'
			)
		),
		'active' => array(
			'bool' => array(
				'rule' => 'boolean',
				'message' => 'Nesprávná hodnota pole Aktivní'
			)
		),
		'email' => array(
			'email' => array(
				'rule' => array('email', true),
				'allowEmpty' => true,
				'message' => 'Email není platný. Zadejte email ve tvaru email@email.cz'
			)
		),
	);
	
	var $export_fields = array(
		array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
		array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
		array('field' => 'BusinessPartner.degree_before', 'position' => '["BusinessPartner"]["degree_before"]', 'alias' => 'BusinessPartner.degree_before'),
		array('field' => 'BusinessPartner.first_name', 'position' => '["BusinessPartner"]["first_name"]', 'alias' => 'BusinessPartner.first_name'),
		array('field' => 'BusinessPartner.last_name', 'position' => '["BusinessPartner"]["last_name"]', 'alias' => 'BusinessPartner.last_name'),
		array('field' => 'BusinessPartner.degree_after', 'position' => '["BusinessPartner"]["degree_after"]', 'alias' => 'BusinessPartner.degree_after'),
		array('field' => 'BusinessPartner.ico', 'position' => '["BusinessPartner"]["ico"]', 'alias' => 'BusinessPartner.ico'),
		array('field' => 'BusinessPartner.dic', 'position' => '["BusinessPartner"]["dic"]', 'alias' => 'BusinessPartner.dic'),
		array('field' => 'BusinessPartner.email', 'position' => '["BusinessPartner"]["email"]', 'alias' => 'BusinessPartner.email'),
		array('field' => 'BusinessPartner.phone', 'position' => '["BusinessPartner"]["phone"]', 'alias' => 'BusinessPartner.phone'),
		array('field' => 'BusinessPartner.note', 'position' => '["BusinessPartner"]["note"]', 'alias' => 'BusinessPartner.note'),
		array('field' => 'BusinessPartner.wallet', 'position' => '["BusinessPartner"]["wallet"]', 'alias' => 'BusinessPartner.wallet'),
		array('field' => 'Address.name', 'position' => '["Address"]["name"]', 'alias' => 'Address.name'),
		array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
		array('field' => 'Address.number', 'position' => '["Address"]["number"]', 'alias' => 'Address.number'),
		array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
		array('field' => 'Address.zip', 'position' => '["Address"]["zip"]', 'alias' => 'Address.zip'),
		array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
	);
	
	function do_form_search($conditions, $data){
		if ( !empty($data['BusinessPartner']['name']) ){
			$conditions[] = 'BusinessPartner.name LIKE \'%%' . $data['BusinessPartner']['name'] . '%%\'';
		}
		if ( !empty($data['BusinessPartner']['ico']) ){
			$conditions[] = 'BusinessPartner.ico LIKE \'%%' . $data['BusinessPartner']['ico'] . '%%\'';
		}
		if ( !empty($data['BusinessPartner']['last_name']) ){
			$conditions[] = 'BusinessPartner.last_name LIKE \'%%' . $data['BusinessPartner']['last_name'] . '%%\'';
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
		$conditions = array();
		if ($term) {
			$conditions['BusinessPartner.name LIKE'] = '%' . $term . '%';
		}
		
		$business_partners = $this->find('all', array(
			'conditions' => $conditions,
			'order' => array('name' => 'asc'),
			'contain' => array(),
			'fields' => array('BusinessPartner.id', 'BusinessPartner.name')
		));
		
		$autocomplete_business_partners = array();
		foreach ($business_partners as $business_partner) {
			$autocomplete_business_partners[] = array(
				'label' => $this->autocomplete_field_info($business_partner['BusinessPartner']['id']),
				'value' => $business_partner['BusinessPartner']['id']
			);
		}
		return json_encode($autocomplete_business_partners);
	}
	
	function autocomplete_field_info($id) {
		$business_partner = $this->find('first', array(
			'conditions' => array('BusinessPartner.id' => $id),
			'contain' => array(
				'Address' => array(
					'conditions' => array('Address.address_type_id' => 1),
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
				'BusinessPartner.id',
				'BusinessPartner.name',
			)
		));
		
		if (empty($business_partner)) {
			return false;
		}
		
		return $business_partner['BusinessPartner']['name'] . ', ' . $business_partner['Address'][0]['street'] . ' ' . $business_partner['Address'][0]['number'] . ', ' . $business_partner['Address'][0]['city'] . ', ' . $business_partner['Address'][0]['zip'];
	}
	
	function wallet_transaction($id, $amount) {
		$business_partner = $this->find('first', array(
			'conditions' => array('BusinessPartner.id' => $id),
			'contain' => array(),
			'fields' => array('BusinessPartner.id', 'BusinessPartner.wallet')
		));
		
		if (empty($business_partner)) {
			return false;
		}
		
		$wallet = $business_partner['BusinessPartner']['wallet'] + $amount;
		$save = array(
			'BusinessPartner' => array(
				'id' => $id,
				'wallet' => $wallet
			)
		);
		
		return $this->save($save);
	}
	
	function contact_people($id) {
		$contact_people = $this->Purchaser->ContactPerson->find('all', array(
			'conditions' => array(
				'ContactPerson.active' => true,
				'Purchaser.active' => true,
				'BusinessPartner.active' => true,
				'BusinessPartner.id' => $id
			),
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('Purchaser.id = ContactPerson.purchaser_id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'INNER',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				)
			),
			'fields' => array('ContactPerson.id', 'ContactPerson.name'),
			'order' => array('ContactPerson.name' => 'asc')
		));
		
		return $contact_people;
	}
}
