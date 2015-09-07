<?php 
class BusinessSessionsCost extends AppModel {
	var $name = 'BusinessSessionsCost';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'BusinessSession',
		'CostType'
	);
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název nákladu'
			)
		),
		'quantity' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
					'message' => 'Zadejte množství u nákladu'
			)
		),
		'price' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte cenu u nákladu'
			)
		)
	);
	
	var $export_fields = array(
		array('field' => 'BusinessSessionsCost.id', 'position' => '["BusinessSessionsCost"]["id"]', 'alias' => 'BusinessSessionsCost.id'),
		array('field' => 'BusinessSessionsCost.quantity', 'position' => '["BusinessSessionsCost"]["quantity"]', 'alias' => 'BusinessSessionsCost.quantity'),
		array('field' => 'BusinessSession.id', 'position' => '["BusinessSession"]["id"]', 'alias' => 'BusinessSession.id'),
		array('field' => 'BusinessSession.date', 'position' => '["BusinessSession"]["date"]', 'alias' => 'BusinessSession.date'),
		array('field' => 'Purchaser.name', 'position' => '["Purchaser"]["name"]', 'alias' => 'Purchaser.name')
	);
	
	function beforeSave() {
		if (array_key_exists('price', $this->data['BusinessSessionsCost'])) {
			$this->data['BusinessSessionsCost']['price'] = str_replace(',', '.', $this->data['BusinessSessionsCost']['price']);
		}
		return true;
	}
	
	function do_form_search($conditions, $data) {
		if (!empty($data['BusinessSessionsCost']['name'])) {
			$conditions[] = 'BusinessSessionsCost.name LIKE \'%%' . $data['BusinessSessionsCost']['name'] . '%%\'';
		}
		return $conditions;
	}
}
?>