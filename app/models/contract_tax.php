<?php
class ContractTax extends AppModel {
	var $name = 'ContractTax';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('Contract');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název daně'
			)
		)
	);
}