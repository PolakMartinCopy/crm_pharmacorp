<?php 
class EducationTypesController extends AppController {
	var $name = 'EducationTypes';
	
	var $left_menu_list = array('settings', 'education_types');
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('active_tab', 'settings');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$this->paginate = array(
			'show' => 'all',
			'conditions' => array('EducationType.active' => true),
			'contain' => array(),
			'order' => array('EducationType.name' => 'asc')
		);
		$education_types = $this->paginate();
		$this->set('education_types', $education_types);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->EducationType->save($this->data)) {
				$this->Session->setFlash('Typ edukace byl uložen.');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Typ edukace se nepodařilo uložit. Opravte chyby ve formuláři a opakujte prosím akci.');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán typ edukace, který chcete upravit');
			$this->redirect(array('action' => 'index'));
		}
		
		$education_type = $this->EducationType->find('first', array(
			'conditions' => array('EducationType.id' => $id),
			'contain' => array()
		));
		
		if (empty($education_type)) {
			$this->Session->setFlash('Typ edukace, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if (isset($this->data)) {
			if ($this->EducationType->save($this->data)) {
				$this->Session->setFlash('Typ edukace byl upraven');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Typ edukace se nepodařilo upravit, opravte chyby ve formuláři a opakujte akci');
			}
		} else {
			$this->data = $education_type;
		}
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán typ edukace, který chcete smazat');
			$this->redirect(array('action' => 'index'));
		}
		
		if (!$this->EducationType->hasAny(array('EducationType.id' => $id))) {
			$this->Session->setFlash('Typ edukace, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if ($this->EducationType->delete($id)) {
			$this->Session->setFlash('Typ edukace byl odstraněn');
		} else {
			$this->Session->setFlash('Typ edukace se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect(array('action' => 'index'));
	}
}
?>