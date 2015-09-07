<?php
class CostTypesController extends AppController {
	var $name = 'CostTypes';
	
	var $index_link = array('controller' => 'cost_types', 'action' => 'index');
	
	var $left_menu_list = array('settings', 'cost_types');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'settings');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$cost_types = $this->CostType->find('all', array(
			'contain' => array()
		));
		
		$this->set('cost_types', $cost_types);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->CostType->save($this->data)) {
				$this->Session->setFlash('Typ obchodního jednání byl uložen');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Typ nákladů se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určen typ nákladů, který chcete upravovat');
			$this->redirect($this->index_link);
		}
		
		$cost_type = $this->CostType->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => array()
		));
		
		if (empty($cost_type)) {
			$this->Session->setFlash('Zvolený typ nákladů neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (isset($this->data)) {
			if ($this->CostType->save($this->data)) {
				$this->Session->setFlash('Typ nákladů byl upraven');
				$this->redirect($this->index_link);
			} else {
				$this->Session->setFlash('Typ nákladů se nepodařilo upravit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $cost_type;
		}
	}
}
