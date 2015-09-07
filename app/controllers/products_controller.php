<?php
class ProductsController extends AppController {
	var $name = 'Products';
	
	var $paginate = array(
		'limit' => 30	
	);
	
	var $left_menu_list = array('products');
	
	function beforeRender() {
		parent::beforeFilter();
		$this->set('active_tab', 'products');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		// reset filtru
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'products') {
			$this->Session->delete('Search.ProductSearch2');
			$this->redirect(array('controller' => 'products', 'action' => 'index'));
		}
		
		// inicializace vyhledavacich podminek
		$conditions = array('Product.active' => true);
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['ProductSearch2']['Product']['search_form']) && $this->data['ProductSearch2']['Product']['search_form'] == 1 ){
			$this->Session->write('Search.ProductSearch2', $this->data['ProductSearch2']);
			$conditions = $this->Product->do_form_search($conditions, $this->data['ProductSearch2']);
		} elseif ($this->Session->check('Search.ProductSearch2')) {
			$this->data['ProductSearch2'] = $this->Session->read('Search.ProductSearch2');
			$conditions = $this->Product->do_form_search($conditions, $this->data['ProductSearch2']);
		}
		
		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array('Unit'),
			'fields' => array('Product.id', 'Product.vzp_code', 'Product.group_code', 'Product.name', 'Product.price', 'Product.education', 'Unit.name'),
			'order' => array('Product.name' => 'asc'),
			'limit' => 40
		);
		
		$products = $this->paginate();

		$find = $this->paginate;
		// parametry pro xls export
		unset($find['limit']);
		unset($find['fields']);
		
		$this->set(compact('products', 'find'));
		$this->set('export_fields', $this->Product->export_fields);
	}
	
	function user_add() {
		if (isset($this->data)) {
			if ($this->Product->save($this->data)) {
				$this->Session->setFlash('Zboží bylo vloženo do číselníku');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Zboží se nepodařilo vložit do číselníku, opravte chyby ve formuláři a opakujte akci');
			}
		}
		
		$units = $this->Product->Unit->find('list', array(
			'order' => array('Unit.name' => 'asc')
		));
		$this->set('units', $units);
	}
	
	function user_edit($id = null) {
		if (!isset($id)) {
			$this->Session->setFlash('Není zadán produkt, který chcete upravovat');
			$this->redirect(array('action' => 'index'));
		}
		
		$product = $this->Product->find('first', array(
			'conditions' => array('Product.id' => $id),
			'contain' => array()
		));
		
		if (empty($product)) {
			$this->Session->setFlash('Produkt, který chcete upravit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if (isset($this->data)) {
			if ($this->Product->save($this->data)) {
				$this->Session->setFlash('Produkt byl upraven');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Produkt se nepodařilo upravit, opravte chyby ve formuláři a opakujte akci');
			}
		} else {
			$this->data = $product;
		}
		
		$units = $this->Product->Unit->find('list', array(
			'order' => array('Unit.name' => 'asc')
		));
		$this->set('units', $units);
	}
	
	function user_delete($id = null) {
		// produkt deaktivuju (soft delete), nemazu!!!
		if (!isset($id)) {
			$this->Session->setFlash('Není zadán produkt, který chcete smazat');
			$this->redirect(array('action' => 'index'));
		}
		
		if (!$this->Product->hasAny(array('Product.id' => $id))) {
			$this->Session->setFlash('Produkt, který chcete smazat, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		if ($this->Product->delete($id)) {
			$this->Session->setFlash('Produkt byl odstraněn');
		} else {
			$this->Session->setFlash('Produkt se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect(array('action' => 'index'));
	}
	
	function user_autocomplete_list() {
		$term = null;
		if ($_GET['term']) {
			$term = $_GET['term'];
		}
		
		echo $this->Product->autocomplete_list($term);
		die();
	}
}
