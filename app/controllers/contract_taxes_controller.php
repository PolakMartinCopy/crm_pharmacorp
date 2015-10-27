<?php
class ContractTaxesController extends AppController {
	var $name = 'ContractTaxes';
	
	var $index_link = array('controller' => 'contract_taxes', 'action' => 'index');
	
	var $left_menu_list = array('settings', 'contract_taxes');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'settings');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$contract_taxes = $this->ContractTax->find('all', array(
			'conditions' => array('active' => true),
			'contain' => array(),
			'order' => array('name' => 'asc')
		));
		$this->set('contract_taxes', $contract_taxes);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->ContractTax->save($this->data)) {
				$this->Session->setFlash('Daň dohody byla uložena');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Daň dohody se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena daň dohody, kterou chcete upravovat');
			$this->redirect($this->index_link);
		}
		
		$contract_tax = $this->ContractTax->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
		
		if (empty($contract_tax)) {
			$this->Session->setFlash('Zvolená daň dohody neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (isset($this->data)) {
			if ($this->ContractTax->save($this->data)) {
				$this->Session->setFlash('Daň dohody byla uložena');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Daň dohody se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $contract_tax;
		}
		
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena daň dohody, kterou chcete smazat');
			$this->redirect($this->index_link);
		}
		
		$contract_tax = $this->ContractTax->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
		
		if (empty($contract_tax)) {
			$this->Session->setFlash('Zvolená daň dohody neexistuje');
			$this->redirect($this->index_link);
		}
		
		$contract_tax['ContractTax']['active'] = false;
		
		if ($this->ContractTax->save($contract_tax)) {
			$this->Session->setFlash('Daň dohody byla odstraněna');
		} else {
			$this->Session->setFlash('Daň dohody se nepodařilo odstranit, opakujte prosím akci');
		}
		
		$this->redirect($this->index_link);
	}
}