<?php
class ContractPaymentsController extends AppController {
	var $name = 'ContractPayments';
	
	var $index_link = array('controller' => 'contract_payments', 'action' => 'index');
	
	var $left_menu_list = array('settings', 'contract_payments');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'settings');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$contract_payments = $this->ContractPayment->find('all', array(
			'contain' => array()
		));
		$this->set('contract_payments', $contract_payments);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->ContractPayment->save($this->data)) {
				$this->Session->setFlash('Platba dohody byla uložena');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Platbu dohody se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena platba dohody, kterou chcete upravovat');
			$this->redirect($this->index_link);
		}
		
		$contract_payment = $this->ContractPayment->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
		
		if (empty($contract_payment)) {
			$this->Session->setFlash('Zvolená platba dohody neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (isset($this->data)) {
			if ($this->ContractPayment->save($this->data)) {
				$this->Session->setFlash('Platba dohody byla uložena');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Platbu dohody se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $contract_payment;
		}
		
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena platba dohody, kterou chcete smazat');
			$this->redirect($this->index_link);
		}
		
		$contract_payment = $this->ContractPayment->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
		
		if (empty($contract_payment)) {
			$this->Session->setFlash('Zvolená platba dohody neexistuje');
			$this->redirect($this->index_link);
		}
		
		if ($this->ContractPayment->delete($id)) {
			$this->Session->setFlash('Platba dohody byla odstraněna');
		} else {
			$this->Session->setFlash('Platbu dohody se nepodařilo odstranit, opakujte prosím akci');
		}
		
		$this->redirect($this->index_link);
	}
}