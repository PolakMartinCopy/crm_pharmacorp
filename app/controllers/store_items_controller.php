<?php
class StoreItemsController extends AppController {
	var $name = 'StoreItems';
	
	var $left_menu_list = array('store_items');
	
	function beforeFilter() {
		$this->Auth->allow('repair');
	}
	
	function beforeRender() {
		parent::beforeRender();
		$this->set('active_tab', 'store_items');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		if (isset($this->params['named']['reset'])) {
			$this->Session->delete('Search.StoreItemForm');
			$this->redirect(array('controller' => 'store_items', 'action' => 'index'));
		}
		
		$conditions = array('Purchaser.active' => true, 'BusinessPartner.active' => true);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions = array_merge($conditions, array('Purchaser.user_id' => $this->user['User']['id']));
		}
		
		// pokud chci vysledky vyhledavani
		if (isset($this->data['StoreItemForm']['StoreItem']['search_form']) && $this->data['StoreItemForm']['StoreItem']['search_form'] == 1){
			$this->Session->write('Search.StoreItemForm', $this->data['StoreItemForm']);
			$conditions = $this->StoreItem->do_form_search($conditions, $this->data['StoreItemForm']);
		} elseif ($this->Session->check('Search.StoreItemForm')) {
			$this->data['StoreItemForm'] = $this->Session->read('Search.StoreItemForm');
			$conditions = $this->StoreItem->do_form_search($conditions, $this->data['StoreItemForm']);
		}

		App::import('Model', 'Address');
		$this->StoreItem->Address = new Address;
		App::import('Model', 'Unit');
		$this->StoreItem->Unit = new Unit;
		App::import('Model', 'BusinessPartner');
		$this->StoreItem->BusinessPartner = new BusinessPartner;

		$this->StoreItem->virtualFields['purchaser_name'] = $this->StoreItem->Purchaser->virtualFields['name'];
		// pomoci strankovani (abych je mohl jednoduse radit) vyberu VSECHNY polozky skladu odberatele
		$this->paginate = $find = array(
			'conditions' => $conditions,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'left',
					'conditions' => array('Purchaser.id = StoreItem.purchaser_id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'left',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
				array(
					'table' => 'addresses',
					'alias' => 'Address',
					'type' => 'left',
					'conditions' => array('Purchaser.id = Address.purchaser_id')
				),
				array(
					'table' => 'products',
					'alias' => 'Product',
					'type' => 'left',
					'conditions' => array('StoreItem.product_id = Product.id')
				),
				array(
					'table' => 'units',
					'alias' => 'Unit',
					'type' => 'left',
					'conditions' => array('Product.unit_id = Unit.id')
					)
			),
			'fields' => array(
				'StoreItem.id',
				'StoreItem.quantity',
				'StoreItem.price',
				'StoreItem.item_total_price',
				'StoreItem.purchaser_name',
				
				'BusinessPartner.id',
				'BusinessPartner.name',
					
				'Purchaser.id',
				
				'Address.city',
				'Address.region',
						
				'Product.id',
				'Product.name',
				'Product.vzp_code',
				'Product.group_code',
							
				'Unit.shortcut'
			),
			'order' => array('StoreItem.modified' => 'desc'),
			'limit' => 30
		);
		$stores = $this->paginate();
		unset($this->StoreItem->virtualFields['purchaser_name']);
		
		$store_items_quantity = $this->StoreItem->getTotalQuantity($this->paginate['conditions'], $this->paginate['contain'], $this->paginate['joins']);
		$store_items_price = $this->StoreItem->getTotalPrice($this->paginate['conditions'], $this->paginate['contain'], $this->paginate['joins']);
		$this->set('store_items_quantity', $store_items_quantity);
		$this->set('store_items_price', $store_items_price);
		
		foreach ($stores as &$store) {
			$store['StoreItem']['week_reserve'] = $this->StoreItem->getWeekReserve($store['StoreItem']['id']);
			$store['StoreItem']['last_sale_date'] = $this->StoreItem->Purchaser->Sale->getLastDate($store['Purchaser']['id'], $store['Product']['id'], true);
		}

		$find = $this->StoreItem->getCSVFind($find);
		unset($find['limit']);
		unset($find['fields']);
		$this->set(compact('find', 'stores'));
		$this->set('export_fields', $this->StoreItem->export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->StoreItem->Purchaser->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
	}
	
	function user_pdf_export() {
		$purchaser_id = $this->data['PDF']['purchaser_id'];
		$purchaser = $this->StoreItem->Purchaser->find('first', array(
			'conditions' => array('Purchaser.id' => $purchaser_id),
			'contain' => array(
				'Address' => array(
					'fields' => array('Address.id', 'Address.street', 'Address.number', 'Address.o_number', 'Address.city', 'Address.zip')
				)
			),
			'fields' => array('Purchaser.id', 'Purchaser.name')
		));
		$user = $this->Auth->user();

		// vyhledam data podle zadanych kriterii
		$store_items = $this->StoreItem->find('all', array(
    		'conditions' => array(
    			'StoreItem.purchaser_id' => $purchaser['Purchaser']['id'],
    			'StoreItem.quantity >' => 0
    		),
			'contain' => array(),
			'joins' => array(
            	array(
                    'table' => 'products',
                    'alias' => 'Product',
                    'type' => 'left',
                    'conditions' => array('StoreItem.product_id = Product.id')
                ),
            	array(
                    'table' => 'units',
                    'alias' => 'Unit',
                    'type' => 'left',
                    'conditions' => array('Product.unit_id = Unit.id')
                )
	        ),
			'order' => array('Product.vzp_code' => 'asc'),
			'fields' => array(
	            'StoreItem.id',
	            'Product.id',
	            'Product.vzp_code',
	            'Product.name',
	            'StoreItem.quantity',
	            'Unit.shortcut',
	            'Product.price',
	            'StoreItem.item_total_price',
	            'Product.group_code'
        	)
		));

		$this->set(compact('purchaser', 'user', 'store_items'));
		
		$this->layout = 'pdf'; //this will use the pdf.ctp layout
	}
	
	function repair() {
		$storeItems = $this->StoreItem->find('all', array(
			'conditions' => array('StoreItem.price' => 0),
			'contain' => array('Product'),
			'fields' => array('StoreItem.id', 'Product.price')
		));
		
		$save = array();
		foreach ($storeItems as &$storeItem) {
			$storeItem['StoreItem']['price'] = $storeItem['Product']['price'];
			$save[] = $storeItem['StoreItem'];
		}
		
		echo $this->StoreItem->saveAll($save);
		die('jhere');
		
		
	}
}
