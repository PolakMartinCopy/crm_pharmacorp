<?php 
class Contract extends AppModel {
	var $name = 'Contract';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'ContractType',
		'ContactPerson',
		'User',
		'BusinessSession'
	);
	
	var $validate = array(
		'begin_date' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte datum zahájení edukace'
			)
		),
		'end_date' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte datum ukončení edukace'
			)
		),
		'month' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte kalendářní měsíc'
			)
		),
		'year' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte kalendářní rok'
			)
		),
		'bank_account' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte číslo bankovního účtu'
			)		
		),
		'amount' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte výši odměny bez DPH',
				'last' => true
			),
			'range' => array(
				'rule' => array('range', 0, 10001),
				'message' => 'Odměna musí být mezi 0 a 10000 Kč'
			)
		),
		'contract_type_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte typ dohody'
			)
		),
		'contact_person_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte kontaktní osobu'
			)
		),
		'number' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Kontaktní osoba nemá zadané číslo popisné, před vytvořením dohody jej zadejte u kontaktní osoby'
			)
		),
		'city' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Kontaktní osoba nemá zadané město, před vytvořením dohody jej zadejte u kontaktní osoby'
			)
		),
		'birthday' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Kontaktní osoba nemá zadané datum narození, před vytvořením dohody jej zadejte u kontaktní osoby'
			)
		),
		'birth_certificate_number' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Kontaktní osoba nemá zadané rodné číslo, před vytvořením dohody jej zadejte u kontaktní osoby'
			)
		),
	);
	
	var $one_line = 'TRIM(CONCAT(Contract.street, " ", Contract.number, ", ", Contract.zip, " ", Contract.city))';
	
	var $vat = 15;
	
	var $export_file = 'files/contracts.csv';
	
	var $export_fields = array(
		array('field' => 'Contract.id', 'position' => '["Contract"]["id"]', 'alias' => 'Contract.id'),
		array('field' => 'Contract.begin_date', 'position' => '["Contract"]["begin_date"]', 'alias' => 'Contract.begin_date'),
		array('field' => 'Contract.end_date', 'position' => '["Contract"]["end_date"]', 'alias' => 'Contract.end_date'),
		array('field' => 'Contract.month', 'position' => '["Contract"]["month"]', 'alias' => 'Contract.mont'),
		array('field' => 'Contract.year', 'position' => '["Contract"]["year"]', 'alias' => 'Contract.year'),
		array('field' => 'Contract.amount', 'position' => '["Contract"]["amount"]', 'alias' => 'Contract.amount'),
		array('field' => 'Contract.amount_vat', 'position' => '["Contract"]["amount_vat"]', 'alias' => 'Contract.amount_vat'),
		array('field' => 'Contract.vat', 'position' => '["Contract"]["vat"]', 'alias' => 'Contract.vat'),
	);
	
	function afterFind($results) {
		foreach ($results as &$result) {
			if (isset($result['Contract']) && array_key_exists('begin_date', $result['Contract'])) {
				$result['Contract']['begin_date'] = db2cal_date($result['Contract']['begin_date']);
			}
			if (isset($result['Contract']) && array_key_exists('end_date', $result['Contract'])) {
				$result['Contract']['end_date'] = db2cal_date($result['Contract']['end_date']);
			}
		}
		return $results;
	}
	
	function beforeSave() {
		if (array_key_exists('begin_date', $this->data['Contract'])) {
			if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{1,4}$/', $this->data['Contract']['begin_date'])) {
				$date = explode('.', $this->data['Contract']['begin_date']);
				$this->data['Contract']['begin_date'] = $date[2] . '-' . $date[1] . '-' . $date[0];
			}
		}
		
		if (array_key_exists('end_date', $this->data['Contract'])) {
			if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{1,4}$/', $this->data['Contract']['end_date'])) {
				$date = explode('.', $this->data['Contract']['end_date']);
				$this->data['Contract']['end_date'] = $date[2] . '-' . $date[1] . '-' . $date[0];
			}
		}
		
		return true;
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
		if ( !empty($data['ContactPerson']['last_name']) ){
			$conditions[] = 'ContactPerson.last_name LIKE \'%%' . $data['ContactPerson']['last_name'] . '%%\'';
		}
		if (!empty($data['Contract']['date_from'])) {
			$date_from = explode('.', $data['Contract']['date_from']);
			$date_from = $date_from[2] . '-' . $date_from[1] . '-' . $date_from[0];
			$conditions['DATE(Contract.created) >='] = $date_from;
		}
		if (!empty($data['Contract']['date_to'])) {
			$date_to = explode('.', $data['Contract']['date_to']);
			$date_to = $date_to[2] . '-' . $date_to[1] . '-' . $date_to[0];
			$conditions['DATE(Contract.created) <='] = $date_to;
		}
		return $conditions;
	}
}
?>