<?php 
class EducationType extends AppModel {
	var $name = 'EducationType';
	
	var $actsAs = array('Containable');
	
	var $hasMany = array('Purchaser');
	
	var $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte název typu edukace'
			)
		)
	);
	
	function delete($id) {
		if ($this->hasAny(array('EducationType.id' => $id))) {
			$save = array(
				'EducationType' => array(
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