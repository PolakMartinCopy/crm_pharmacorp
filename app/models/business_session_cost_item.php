<?php
class BusinessSessionCostItem extends AppModel {
	var $name = 'BusinessSessionCostItem';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('BusinessSessionsCost');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název nákladu'
			)
		),
		'price' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte cenu nákladu za jednotku'
			)
		)
	);
	
	var $export_file = 'files/business_session_cost_items.csv';
	
	// pole pro xls export
	var $export_fields = array(
		array('field' => 'BusinessSessionCostItem.id', 'position' => '["BusinessSessionCostItem"]["id"]', 'alias' => 'BusinessSessionCostItem.id'),
		array('field' => 'BusinessSessionCostItem.name', 'position' => '["BusinessSessionCostItem"]["name"]', 'alias' => 'BusinessSessionCostItem.name'),
		array('field' => 'BusinessSessionCostItem.price', 'position' => '["BusinessSessionCostItem"]["price"]', 'alias' => 'BusinessSessionCostItem.price'),
		array('field' => 'BusinessSessionCostItem.quantity', 'position' => '["BusinessSessionCostItem"]["quantity"]', 'alias' => 'BusinessSessionCostItem.quantity')
	);
	
	function beforeSave() {
		if (isset($this->data['BusinessSessionCostItem']['price'])) {
			$this->data['BusinessSessionCostItem']['price'] = str_replace(',', '.', $this->data['BusinessSessionCostItem']['price']);
		}

		return true;
	}
	
	// metoda pro smazani produktu - NEMAZE ale DEAKTIVUJE
	function delete($id = null) {
		if (!$id) {
			return false;
		}
	
		if ($this->hasAny(array('BusinessSessionCostItem.id' => $id))) {
			$item = array(
				'BusinessSessionCostItem' => array(
					'id' => $id,
					'active' => false
				)
			);
			return $this->save($item);
		} else {
			return false;
		}
	}
	
	function do_form_search($conditions, $data) {
		if (!empty($data['BusinessSessionCostItem']['name'])) {
			$conditions[] = 'BusinessSessionCostItem.name LIKE \'%%' . $data['BusinessSessionCostItem']['name'] . '%%\'';
		}
	
		return $conditions;
	}
	
	function autocomplete_list($term = null) {
		$conditions = array('BusinessSessionCostItem.active' => true);
		if ($term) {
			$conditions['BusinessSessionCostItem.name LIKE'] = '%' . $term . '%';
		}
	
		$items = $this->find('all', array(
			'conditions' => $conditions,
			'contain' => array(),
			'fields' => array('BusinessSessionCostItem.id', 'BusinessSessionCostItem.name', 'BusinessSessionCostItem.price')
		));
	
		$autocomplete_list = array();
		foreach ($items as $item) {
			$autocomplete_list[] = array(
				'label' => $item['BusinessSessionCostItem']['name'],
				'value' => $item['BusinessSessionCostItem']['id'],
				'price' => $item['BusinessSessionCostItem']['price']
			);
		}
		return json_encode($autocomplete_list);
	}
}