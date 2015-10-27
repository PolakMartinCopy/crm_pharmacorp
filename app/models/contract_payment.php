<?php
class ContractPayment extends AppModel {
	var $name = 'ContractPayment';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('Contract');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název platby dohody'
			)
		)
	);
}