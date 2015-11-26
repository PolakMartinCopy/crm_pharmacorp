<?php
class BusinessSessionCostItemsController extends AppController {
	var $name = 'BusinessSessionCostItems';
	
	var $left_menu_list = array('business_session_cost_items');
	
	function beforeRender() {
		parent::beforeFilter();
		$this->set('active_tab', 'business_session_cost_items');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		// reset filtru
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'business_session_cost_items') {
			$this->Session->delete('Search.BusinessSessionCostItemSearch');
			$this->redirect(array('controller' => 'business_session_cost_items', 'action' => 'index'));
		}
		
		// inicializace vyhledavacich podminek
		$conditions = array('BusinessSessionCostItem.active' => true);
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['BusinessSessionCostItemSearch']['BusinessSessionCostItem']['search_form']) && $this->data['BusinessSessionCostItemSearch']['BusinessSessionCostItem']['search_form'] == 1 ){
			$this->Session->write('Search.BusinessSessionCostItemSearch', $this->data['BusinessSessionCostItemSearch']);
			$conditions = $this->BusinessSessionCostItem->do_form_search($conditions, $this->data['BusinessSessionCostItemSearch']);
		} elseif ($this->Session->check('Search.BusinessSessionCostItemSearch')) {
			$this->data['BusinessSessionCostItemSearch'] = $this->Session->read('Search.BusinessSessionCostItemSearch');
			$conditions = $this->BusinessSessionCostItem->do_form_search($conditions, $this->data['BusinessSessionCostItemSearch']);
		}
		
		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array(),
			'order' => array('BusinessSessionCostItem.name' => 'asc'),
			'limit' => 40
		);
		
		$items = $this->paginate();
		
		$find = $this->paginate;
		// parametry pro xls export
		unset($find['limit']);
		unset($find['fields']);
		
		$this->set(compact('items', 'find'));
		$this->set('export_fields', $this->BusinessSessionCostItem->export_fields);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->BusinessSessionCostItem->save($this->data)) {
				$this->Session->setFlash('Náklad byl vložen do číselníku');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Náklad se nepodařilo vložit do číselníku, opravte chyby ve formuláři a opakujte akci');
			}
		}
	}
	
	function user_edit($id = null) {
		if (!isset($id)) {
			$this->Session->setFlash('Není zadán náklad, který chcete upravovat');
			$this->redirect(array('action' => 'index'));
		}
		
		$item = $this->BusinessSessionCostItem->find('first', array(
			'conditions' => array('BusinessSessionCostItem.id' => $id),
			'contain' => array()
		));
		
		if (empty($item)) {
			$this->Session->setFlash('Náklad, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if (isset($this->data)) {
			if ($this->BusinessSessionCostItem->save($this->data)) {
				$this->Session->setFlash('Náklad byl upraven');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Náklad se nepodařilo upravit, opravte chyby ve formuláři a opakujte akci');
			}
		} else {
			$this->data = $item;
		}
	}
	
	function user_delete($id = null) {
		// produkt deaktivuju (soft delete), nemazu!!!
		if (!isset($id)) {
			$this->Session->setFlash('Není zadán náklad, který chcete smazat');
			$this->redirect(array('action' => 'index'));
		}
		
		if (!$this->BusinessSessionCostItem->hasAny(array('BusinessSessionCostItem.id' => $id))) {
			$this->Session->setFlash('Náklad, který chcete smazat, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if ($this->BusinessSessionCostItem->delete($id)) {
			$this->Session->setFlash('Náklad byl odstraněn');
		} else {
			$this->Session->setFlash('Náklad se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect(array('action' => 'index'));
	}
	
	function user_autocomplete_list() {
		$term = null;
		if ($_GET['term']) {
			$term = $_GET['term'];
		}
		
		echo $this->BusinessSessionCostItem->autocomplete_list($term);
		die();
	}
}