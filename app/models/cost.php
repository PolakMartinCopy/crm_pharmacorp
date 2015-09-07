<?php
class Cost extends AppModel {
	var $name = 'Cost';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array('BusinessSession');
	
	var $validate = array(
		'amount' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				'allowEmpty' => false,
				'message' => 'Částka musí být desetinné číslo'
			)
		),
		'date' => array(
			'rule' => 'date',
			'allowEmpty' => false,
			'message' => 'Datum musí být vyplněno'
		),
		'business_session_id' => array(
			'rule' => 'numeric',
			'allowEmpty' => 'false',
			'message' => 'Obchodní jednání musí být vybráno'
		)
	);
	
	var $export_fields = array(
		array('field' => 'Cost.id', 'position' => '["Cost"]["id"]', 'alias' => 'Cost.id'),
		array('field' => 'Cost.date', 'position' => '["Cost"]["date"]', 'alias' => 'Cost.date'),
		array('field' => 'Cost.amount', 'position' => '["Cost"]["amount"]', 'alias' => 'Cost.amount'),
		array('field' => 'Cost.description', 'position' => '["Cost"]["description"]', 'alias' => 'Cost.description'),
		array('field' => 'BusinessSession.id', 'position' => '["BusinessSession"]["id"]', 'alias' => 'BusinessSession.id'),
		array('field' => 'Purchaser.name', 'position' => '["Purchaser"]["name"]', 'alias' => 'Purchaser.name')
	);
	
	function do_form_search($conditions, $data) {
		if (!empty($data['Cost']['date'])) {
			$date = explode('.', $data['Cost']['date']);
			$date = $date[2] . '-' . $date[1] . '-' . $date[0];
			$conditions[] = 'Cost.date LIKE \'%%' . $date . '%%\'';
		}
		if (!empty($data['Cost']['description'])) {
			$conditions[] = 'Cost.description LIKE \'%%' . $data['Cost']['description'] . '%%\'';
		}
		if (!empty($data['Cost']['amount_from'])) {
			$conditions[] = 'Cost.amount >= ' . $data['Cost']['amount_from'];
		}
		if (!empty($data['Cost']['amount_to'])) {
			$conditions[] = 'Cost.amount <= ' . $data['Cost']['amount_to'];
		}
		return $conditions;
	}
}
