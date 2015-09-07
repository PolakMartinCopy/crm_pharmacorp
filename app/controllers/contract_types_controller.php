<?php
class ContractTypesController extends AppController {
	var $name = 'ContractTypes';
	
	var $index_link = array('controller' => 'contract_types', 'action' => 'index');
	
	var $left_menu_list = array('settings', 'contract_types');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'settings');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$contract_types = $this->ContractType->find('all', array(
			'contain' => array()
		));
	
		$this->set('contract_types', $contract_types);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->ContractType->save($this->data)) {
				$this->Session->setFlash('Typ dohody byl uložen');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Typ dohody se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určen typ dohody, který chcete upravovat');
			$this->redirect($this->index_link);
		}
	
		$contract_type = $this->ContractType->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
	
		if (empty($contract_type)) {
			$this->Session->setFlash('Zvolený typ dohody neexistuje');
			$this->redirect($this->index_link);
		}
	
		if (isset($this->data)) {
			if ($this->ContractType->save($this->data)) {
				$this->Session->setFlash('Typ dohody byl upraven');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Typ dohody se nepodařilo upravit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $contract_type;
		}
	}
}
?>