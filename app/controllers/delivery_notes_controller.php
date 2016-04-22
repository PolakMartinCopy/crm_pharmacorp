<?php
App::import('Controller', 'Transactions');
class DeliveryNotesController extends TransactionsController {
	var $name = 'DeliveryNotes';
	
	var $left_menu_list = array('delivery_notes');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'delivery_notes');
		$this->set('left_menu_list', $this->left_menu_list);
		
		$this->Auth->allow('view_pdf', 'test');
	}
	
	function user_add() {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'delivery_notes', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 10);
		}
		$this->set('redirect', $redirect);
		
		if (isset($this->data)) {
			if (isset($this->data['ProductsTransaction'])) {
				foreach ($this->data['ProductsTransaction'] as $index => $products_transaction) {
					if (empty($products_transaction['product_id']) && empty($products_transaction['quantity'])) {
						unset($this->data['ProductsTransaction'][$index]);
					} else {
						$this->data['ProductsTransaction'][$index]['purchaser_id'] = $this->data['DeliveryNote']['purchaser_id'];
					}
				}
				if (empty($this->data['ProductsTransaction'])) {
					$this->Session->setFlash('Dodací list neobsahuje žádné produkty a nelze jej proto uložit');
				} else {

					if ($this->DeliveryNote->saveAll($this->data, array('validate' => 'only'))) {
						if ($this->DeliveryNote->saveAll($this->data)) {
							
							// vytvorim pdf dodaciho listu
							$this->DeliveryNote->pdf_generate($this->DeliveryNote->id);
							
							$this->Session->setFlash('Dodací list byl uložen.');
							$this->redirect($redirect);
						}
					} else {
						$this->Session->setFlash('Dodací list se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
					}
				}
			} else {
				$this->Session->setFlash('Dodací list neobsahuje žádné produkty a nelze jej proto uložit');
			}
		} else {
			$this->data['DeliveryNote']['date'] = date('d.m.Y');
		}
		// pokud jsem na form pro pridani prisel z detailu obchodniho partnera, predvyplnim pole
		if (isset($this->params['named']['purchaser_id'])) {
			$purchaser = $this->DeliveryNote->Purchaser->find('first', array(
				'conditions' => array('Purchaser.id' => $this->params['named']['purchaser_id']),
				'contain' => array(),
				'fields' => array('Purchaser.id', 'Purchaser.name')
			));
			$this->set('purchaser', $purchaser);
			$this->data['DeliveryNote']['purchaser_name'] = $purchaser['Purchaser']['name'];
			$this->data['DeliveryNote']['purchaser_id'] = $purchaser['Purchaser']['id'];
		}
		
		$this->set('user', $this->user);
		$shippings = $this->DeliveryNote->Shipping->findList();
		$this->set('shippings', $shippings);
	}

	function view_pdf($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán dodaci list, který chcete zobrazit');
			$this->redirect(array('action' => 'index'));
		}

		if (!$this->DeliveryNote->hasAny(array('DeliveryNote.id' => $id))) {
			$this->Session->setFlash('Dodací list, který chcete zobrazit, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		$this->DeliveryNote->virtualFields['purchaser_name'] = $this->DeliveryNote->Purchaser->virtualFields['name'];
		$delivery_note = $this->DeliveryNote->find('first', array(
			'conditions' => array('DeliveryNote.id' => $id),
			'contain' => array(
				'ProductsTransaction' => array(
					'fields' => array('ProductsTransaction.id', 'ProductsTransaction.quantity', 'ProductsTransaction.product_name', 'ProductsTransaction.lot', 'ProductsTransaction.exp'),
					'Product' => array(
						'fields' => array('Product.id', 'Product.vzp_code'),
						'Unit' => array(
							'fields' => array('Unit.id', 'Unit.shortcut')
						)
					)
				),
				'User' => array(
					'fields' => array('User.id', 'User.first_name', 'User.last_name')
				),
				'TransactionType'
			),
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'left',
					'conditions' => array('DeliveryNote.purchaser_id = Purchaser.id')	
				),
				array(
					'table' => 'addresses',
					'alias' => 'Address',
					'type' => 'left',
					'conditions' => array('Purchaser.id = Address.purchaser_id')
				)
			),
			'fields' => array(
				'DeliveryNote.id',
				'DeliveryNote.date',
				'DeliveryNote.time',
				'DeliveryNote.code',
				'DeliveryNote.purchaser_name',
				'DeliveryNote.purchaser_id',
					
				'Address.id',
				'Address.street',
				'Address.number',
				'Address.o_number',
				'Address.city',
				'Address.zip'
			)
		));
		unset($this->DeliveryNote->virtualFields['purchaser_name']);
		$this->set('delivery_note', $delivery_note);

		// aktualni stav skladu odberatele
		$store_items = $this->DeliveryNote->Purchaser->StoreItem->find('all', array(
			'conditions' => array(
				'StoreItem.purchaser_id' => $delivery_note['DeliveryNote']['purchaser_id'],
				'StoreItem.quantity >' => 0
			),
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'products',
					'alias' => 'Product',
					'type' => 'LEFT',
					'conditions' => array('StoreItem.product_id = Product.id')
				),
				array(
					'table' => 'units',
					'alias' => 'Unit',
					'type' => 'left',
					'conditions' => array('Product.unit_id = Unit.id')
				)
			),
			'fields' => array('StoreItem.id', 'StoreItem.quantity', 'Product.id', 'Product.name', 'Product.vzp_code', 'Unit.id', 'Unit.shortcut')
		));
		$this->set('store_items', $store_items);

		$this->layout = 'pdf'; //this will use the pdf.ctp layout
	}
	
	function test() {
		$ids = array(34, 35, 37, 39, 40);
		foreach ($ids as $id) {
			$this->DeliveryNote->pdf_generate($id);
		}
		die('asd');
	}
}
