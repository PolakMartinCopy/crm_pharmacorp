<?php 
class PurchaserTypesController extends AppController {
	var $name = 'PurchaserTypes';
	
	var $left_menu_list = array('settings', 'purchaser_types');
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('active_tab', 'settings');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$this->paginate = array(
			'show' => 'all',
			'conditions' => array('PurchaserType.active' => true),
			'contain' => array(),
			'order' => array('PurchaserType.name' => 'asc')
		);
		$purchaser_types = $this->paginate();
		$this->set('purchaser_types', $purchaser_types);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->PurchaserType->save($this->data)) {
				$this->Session->setFlash('Typ odběratele byl uložen.');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Typ odběratele se nepodařilo uložit. Opravte chyby ve formuláři a opakujte prosím akci.');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán typ odběratele, který chcete upravit');
			$this->redirect(array('action' => 'index'));
		}
		
		$purchaser_type = $this->PurchaserType->find('first', array(
			'conditions' => array('PurchaserType.id' => $id),
			'contain' => array()
		));
		
		if (empty($purchaser_type)) {
			$this->Session->setFlash('Typ odběratele, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if (isset($this->data)) {
			if ($this->PurchaserType->save($this->data)) {
				$this->Session->setFlash('Typ odběratele byl upraven');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Typ odběratele se nepodařilo upravit, opravte chyby ve formuláři a opakujte akci');
			}
		} else {
			$this->data = $purchaser_type;
		}
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán typ odběratele, který chcete smazat');
			$this->redirect(array('action' => 'index'));
		}
		
		if (!$this->PurchaserType->hasAny(array('PurchaserType.id' => $id))) {
			$this->Session->setFlash('Typ odběratele, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if ($this->PurchaserType->delete($id)) {
			$this->Session->setFlash('Typ odběratele byl odstraněn');
		} else {
			$this->Session->setFlash('Typ odběratele se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect(array('action' => 'index'));
	}
}
?>