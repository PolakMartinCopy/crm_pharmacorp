<?php 
class PurchaserType extends AppModel {
	var $name = 'PurchaserType';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('Purchaser');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název typu odběratele'
			)
		)
	);
	
	function delete($id) {
		if ($this->hasAny(array('PurchaserType.id' => $id))) {
			$save = array(
				'PurchaserType' => array(
					'id' => $id,
					'active' => false
				)
			);
			return $this->save($save);
		}
		return false;
	}
}
?>