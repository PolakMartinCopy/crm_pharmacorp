<?php
class Offer extends AppModel {
	var $name = 'Offer';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array('BusinessSession');
	
	var $hasMany = array(
		'Document' => array('dependent' => true)
	);
	
	var $validate = array(
		'business_session_id' => array(
			'numeric' => array(
				'rule' => 'numeric',
				'allowEmpty' => false,
				'message' => 'Není zadáno obchodní jednání'
			)
		),
		'content' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Obsah obchodního jednání musí být zadán'
			)
		)
	);
	
	var $export_fields = array(
		array('field' => 'Offer.id', 'position' => '["Offer"]["id"]', 'alias' => 'Offer.id'),
		array('field' => 'Offer.created', 'position' => '["Offer"]["created"]', 'alias' => 'Offer.created'),
		array('field' => 'Offer.content', 'position' => '["Offer"]["content"]', 'alias' => 'Offer.content'),
		array('field' => 'BusinessSession.id', 'position' => '["BusinessSession"]["id"]', 'alias' => 'BusinessSession.id'),
		array('field' => 'Purchaser.name', 'position' => '["Purchaser"]["name"]', 'alias' => 'Purchaser.name'),
	);
	
	function do_form_search($conditions, $data) {
		if (!empty($data['Offer']['created'])) {
			$date = explode('.', $data['Offer']['created']);
			$date = $date[2] . '-' . $date[1] . '-' . $date[0];
			$conditions[] = 'Offer.created LIKE \'%%' . $date . '%%\'';
		}
		if (!empty($data['Offer']['content'])) {
			$conditions[] = 'Offer.content LIKE \'%%' . $data['Offer']['content'] . '%%\'';
		}
		return $conditions;
	}
}
