<?php 
class ContractType extends AppModel {
	var $name = 'ContractType';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('Contract');
	
	var $validate = array(
		'text' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte text typu dohody'
			)
		)
	);

	var $virtualFields = array(
		'name' => 'IF(LENGTH(ContractType.text)>100, CONCAT(SUBSTR(ContractType.text, 1, 100), "..."), ContractType.text)'
	);
	
	var $order = array('ContractType.id' => 'asc');
}
?>