<?php 
class Contract extends AppModel {
	var $name = 'Contract';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'ContractType',
		'ContactPerson',
		'User',
		'BusinessSession',
		'ContractPayment',
		'ContractTax'
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
			'notEmptyIfTransfer' => array(
				'rule' => array('notEmptyBankAccoutIfTransfer'),
				'message' => 'Zadejte číslo bankovního účtu'
			)		
		),
		'amount_vat' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte výši odměny s DPH',
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
	var $street_info = 'TRIM(CONCAT(Contract.street, " ", Contract.number))';
	var $vat_money = 'Contract.amount_vat - Contract.amount';
	var $strips_count = 'ROUND(Contract.amount_vat/30)';
	var $price_per_hour = 'ROUND(Contract.amount_vat/25)';
	
	var $vat = 15;
	
	var $export_file = 'files/contracts.csv';
	
	var $export_fields = array(
		array('field' => 'Contract.id', 'position' => '["Contract"]["id"]', 'alias' => 'Contract.id'),
		array('field' => 'User.first_name', 'position' => '["User"]["first_name"]', 'alias' => 'OZ'),
		array('field' => 'ContactPerson.first_name', 'position' => '["ContactPerson"]["first_name"]', 'alias' => 'Jméno'),
		array('field' => 'ContactPerson.last_name', 'position' => '["ContactPerson"]["last_name"]', 'alias' => 'Příjmení'),
		array('field' => 'Contract.birthday', 'position' => '["Contract"]["birthday"]', 'alias' => 'Datum narození'),
		array('field' => 'Contract.birth_certificate_number', 'position' => '["Contract"]["birth_certificate_number"]', 'alias' => 'Rodné číslo'),
		array('field' => 'Contract.street_info', 'position' => '["Contract"]["street_info"]', 'alias' => 'Bydliště'),
		array('field' => 'Contract.city', 'position' => '["Contract"]["city"]', 'alias' => 'Město'),
		array('field' => 'Contract.zip', 'position' => '["Contract"]["zip"]', 'alias' => 'PSČ'),
		array('field' => 'Contract.bank_account', 'position' => '["Contract"]["bank_account"]', 'alias' => 'Číslo účtu'),
		array('field' => 'Contract.month', 'position' => '["Contract"]["month"]', 'alias' => 'Období'),
		array('field' => 'Contract.amount_vat', 'position' => '["Contract"]["amount_vat"]', 'alias' => 'Odměna'),
		array('field' => 'Contract.amount', 'position' => '["Contract"]["amount"]', 'alias' => 'Vyplatit'),
		array('field' => 'Contract.vat_money', 'position' => '["Contract"]["vat_money"]', 'alias' => 'Daň'),
		array('field' => 'Contract.strips_count', 'position' => '["Contract"]["strips_count"]', 'alias' => ' Počet proužků'),
		array('field' => 'Contract.price_per_hour', 'position' => '["Contract"]["price_per_hour"]', 'alias' => 'Cena za hodinu'),
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
	
	// datum splatnosti je 10. den mesice, ktery nasleduje po tom, pro ktery byla dohoda vyhotovena
	function due_date($id) {
		$contract = $this->find('first', array(
			'conditions' => array('Contract.id' => $id),
			'contain' => array(),
			'fields' => array('Contract.id', 'Contract.month', 'Contract.year')
		));
		$month = $contract['Contract']['month'];
		if (strlen($month) == 1) {
			$month = '0' . $month;
		}
		$date = $contract['Contract']['year'] . '-' . $month . '-10';
		$date = date('d.m.Y', strtotime('+1 month' , strtotime($date)));
		return $date;
	}
	
	// datum splatnosti je posledni den mesice, pro ktery byla dohoda vyhotovena
	function signature_date($id) {
		$contract = $this->find('first', array(
			'conditions' => array('Contract.id' => $id),
			'contain' => array(),
			'fields' => array('Contract.id', 'Contract.month', 'Contract.year')
		));
		
		$month = $contract['Contract']['month'];
		if (strlen($month) == 1) {
			$month = '0' . $month;
		}
		// prvni den v mesici
		$first_day_date = $contract['Contract']['year'] . '-' . $month . '-01';
		// pocet dni v mesici
		$number_of_days = $maxDays=date('t', strtotime($first_day_date));

		$date = $contract['Contract']['year'] . '-' . $month . '-' . $number_of_days;
		$date = date('d.m.Y', strtotime($date));
		return $date;
	}
	
	function notEmptyBankAccoutIfTransfer() {
		$isTransfer = (isset($this->data['Contract']['contract_payment_id']) && $this->data['Contract']['contract_payment_id'] == 1);
		$hasBankAccount = (isset($this->data['Contract']['bank_account']) && !empty($this->data['Contract']['bank_account']));
		return !$isTransfer || $hasBankAccount;
	}
}
?>