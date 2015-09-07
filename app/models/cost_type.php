<?php 
class CostType extends AppModel {
	var $name = 'CostType';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('BusinessSessionsCost');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název typu nákladu'
			)
		)
	);
	
	var $order = array('FIELD (CostType.id, 1, 2, 3, 4, 5)');
}
?>