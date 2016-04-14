<?php 
App::import('Controller', 'Transactions');
class SalesController extends TransactionsController {
	var $name = 'Sales';
	
	var $left_menu_list = array('sales');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'sales');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_add() {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'sales', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 11);
		}
		$this->set('redirect', $redirect);
		
		if (isset($this->data)) {
			if (isset($this->data['ProductsTransaction'])) {
				// odstranim z formu prazdne radky pro vlozeni produktu
				foreach ($this->data['ProductsTransaction'] as $index => &$products_transaction) {
					if (empty($products_transaction['product_id']) && empty($products_transaction['quantity'])) {
						unset($this->data['ProductsTransaction'][$index]);
					} else {
						// k produktum si zapamatuju id odberatele
						$products_transaction['purchaser_id'] = $this->data['Sale']['purchaser_id'];
						$products_transaction['quantity'] =  -$products_transaction['quantity'];
					}
				}
				// pokud nemam zadne radky s produkty, neulozim
				if (empty($this->data['ProductsTransaction'])) {
					$this->Session->setFlash('Poukaz neobsahuje žádné produkty a nelze jej proto uložit');
				} else {
					if ($this->Sale->saveAll($this->data, array('validate' => 'only'))) {
						if ($this->Sale->saveAll($this->data)) {
							$this->Session->setFlash('Poukaz byl uložen.');
							$this->redirect($redirect);
						}
					} else {
						foreach ($this->data['ProductsTransaction'] as &$products_transaction) {
							$products_transaction['quantity'] =  -$products_transaction['quantity'];
						}
						$this->Session->setFlash('Poukaz se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
					}
				}
			} else {
				$this->Session->setFlash('Poukaz neobsahuje žádné produkty a nelze jej proto uložit');
			}
		} else {
			$this->data['Sale']['date'] = date('d.m.Y');
		}
		// pokud jsem na form pro pridani prisel z detailu obchodniho partnera, predvyplnim pole
		if (isset($this->params['named']['purchaser_id'])) {
			$purchaser = $this->Sale->Purchaser->find('first', array(
				'conditions' => array('Purchaser.id' => $this->params['named']['purchaser_id']),
				'contain' => array(),
				'fields' => array('Purchaser.id', 'Purchaser.name')
			));
			$this->set('purchaser', $purchaser);
			$this->data['Sale']['purchaser_name'] = $purchaser['Purchaser']['name'];
			$this->data['Sale']['purchaser_id'] = $purchaser['Purchaser']['id'];
		}
		
		$this->set('user', $this->user);
	}
}
?>
