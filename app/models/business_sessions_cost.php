<?php 
class BusinessSessionsCost extends AppModel {
	var $name = 'BusinessSessionsCost';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'BusinessSession',
		'CostType',
		'BusinessSessionCostItem'
	);
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název nákladu'
			)
		),
		'business_session_cost_item_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Vyberte náklad z nabízených'
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
		
		// musim odecist pocet ze skladu
		if (
			array_key_exists('business_session_cost_item_id', $this->data['BusinessSessionsCost'])
			&& array_key_exists('quantity', $this->data['BusinessSessionsCost'])
		) {
			$cost_item_id = $this->data['BusinessSessionsCost']['business_session_cost_item_id'];
			$cost_item = $this->BusinessSessionCostItem->find('first', array(
				'conditions' => array('BusinessSessionCostItem.id' => $cost_item_id),
				'contain' => array(),
				'fields' => array('BusinessSessionCostItem.id', 'BusinessSessionCostItem.quantity')
			));
			if (!empty($cost_item)) {
				$cost_item['BusinessSessionCostItem']['quantity'] -= $this->data['BusinessSessionsCost']['quantity'];
				$this->BusinessSessionCostItem->save($cost_item);
			}
		}
		return true;
	}
	
	// musim si zapamatovat, co mazu, abych to mohl po smazani odecist ze skladu odberatele
	function beforeDelete() {
		$cost = $this->find('first', array(
			'conditions' => array('BusinessSessionsCost.id' => $this->id),
			'contain' => array(
				'BusinessSessionCostItem' => array(
					'fields' => array('BusinessSessionCostItem.id', 'BusinessSessionCostItem.quantity')
				)
			),
		));
		
		if (isset($cost['BusinessSessionCostItem']['id'])) {
			$cost['BusinessSessionCostItem']['quantity'] += $cost['BusinessSessionsCost']['quantity'];
			return $this->BusinessSessionCostItem->save($cost);
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