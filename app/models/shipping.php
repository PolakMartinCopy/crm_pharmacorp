<?php
class Shipping extends AppModel {
	var $name = 'Shipping';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('DeliveryNote');
	
	function findList() {
		$list = $this->find('list', array(
			'order' => array('FIELD(Shipping.id, 2, 1)')
		));
		return $list;
	}
}