<?php 
class PurchasersController extends AppController {
	var $name = 'Purchasers';
	
	var $index_link = array('controller' => 'purchasers', 'action' => 'index');
	
	var $paginate = array(
			'limit' => 30,
			'order' => array('BusinessPartner.name' => 'asc'),
	);
	
	var $bonity = array(1 => 'A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'C1', 'C2', 'C3');
	
	// zakladni nastaveni pro leve menu
	// v konkretni action se da pridat,
	// nebo upravit
	var $left_menu_list = array('purchasers');
	
	function beforeFilter(){
		parent::beforeFilter();
		$this->set('active_tab', 'purchasers');
		$this->Auth->allow('store_items');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		if (!isset($this->data)) {
			$this->data = array();
		}
		
		// pokud chce uzivatel resetovat filtr
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'purchasers') {
			$this->Session->delete('Search.PurchaserSearch');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'index'));
		}
		
		$conditions = array('Purchaser.active' => true, 'BusinessPartner.active' => true);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}
		
			// pokud chci vysledky vyhledavani
		if (isset($this->data['PurchaserSearch']['Purchaser']['search_form']) && $this->data['PurchaserSearch']['Purchaser']['search_form'] == 1) {
			$this->Session->write('Search.PurchaserSearch', $this->data['PurchaserSearch']);
			$conditions = $this->Purchaser->do_form_search($conditions, $this->data['PurchaserSearch']);
		} elseif ($this->Session->check('Search.PurchaserSearch')) {
			$this->data['PurchaserSearch'] = $this->Session->read('Search.PurchaserSearch');
			$conditions = $this->Purchaser->do_form_search($conditions, $this->data['PurchaserSearch']);
		}

		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array(),
			'limit' => 30,
			'fields' => array('*'),
			'joins' => array(
				array(
					'table' => 'addresses',
					'type' => 'LEFT',
					'alias' => 'Address',
					'conditions' => array('Purchaser.id = Address.purchaser_id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'LEFT',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => array('Purchaser.user_id = User.id')
				)
			)
		);
		
		$purchasers = $this->paginate();

		$this->set('purchasers', $purchasers);
		
		$find = $this->paginate;
		unset($find['limit']);
		unset($find['fields']);
		$this->set('find', $find);
		
		$this->set('export_fields', $this->Purchaser->export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->Purchaser->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
	}
	
	function user_view($id = null) {
		$sort_field = '';
		if (isset($this->passedArgs['sort'])) {
			$sort_field = $this->passedArgs['sort'];
		} 
		
		$sort_direction = '';
		if (isset($this->passedArgs['direction'])) {
			$sort_direction = $this->passedArgs['direction'];
		}

		// do leveho menu pridam polozku pro detaily partnera
		$this->left_menu_list[] = 'purchaser_detailed';
		
		if (!$id) {
			$this->Session->setFlash('Není určen odběratel, kterého chcete zobrazit');
			$this->redirect($this->index_link);
		}
		
		$conditions = array(
			'Purchaser.id' => $id,
			'Purchaser.active' => true
		);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}
		
		$this->Purchaser->virtualFields['full_title'] = $this->Purchaser->full_title;
		$this->Purchaser->virtualFields['user_full_name'] = $this->Purchaser->User->full_name;
		$this->Purchaser->virtualFields['address_full_name'] = $this->Purchaser->Address->full_name;
		$this->Purchaser->virtualFields['address_one_line'] = $this->Purchaser->Address->one_line;
		$purchaser = $this->Purchaser->find('first', array(
			'conditions' => $conditions,
			'contain' => array(
				'Address',
				'User',
				'BusinessPartner'
			)
		));
		unset($this->Purchaser->virtualFields['full_title']);
		unset($this->Purchaser->virtualFields['user_full_name']);
		unset($this->Purchaser->virtualFields['address_full_name']);
		unset($this->Purchaser->virtualFields['address_one_line']);

		if (empty($purchaser)) {
			$this->Session->setFlash('Zvolený odběratel neexistuje');
			$this->redirect($this->index_link);
		}
		
		$this->set('bonity', $this->bonity);
		
		// KONTAKTNI OSOBY TOHOTO ODBERATELE
		$contact_people_conditions = array(
			'ContactPerson.purchaser_id' => $id,
			'ContactPerson.active' => true
		);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'contact_people') {
			$this->Session->delete('Search.ContactPersonSearch');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $id, 'tab' => 7));
		}

		// pokud chci vysledky vyhledavani
		if ( isset($this->data['ContactPersonSearch']['ContactPerson']['search_form']) && $this->data['ContactPersonSearch']['ContactPerson']['search_form'] == 1 ){
			$this->Session->write('Search.ContactPersonSearch', $this->data['ContactPersonSearch']);
			$contact_people_conditions = $this->Purchaser->ContactPerson->do_form_search($contact_people_conditions, $this->data['ContactPersonSearch']);
		} elseif ($this->Session->check('Search.ContactPersonSearch')) {
			$this->data['ContactPersonSearch'] = $this->Session->read('Search.ContactPersonSearch');
			$contact_people_conditions = $this->Purchaser->ContactPerson->do_form_search($contact_people_conditions, $this->data['ContactPersonSearch']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 7) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}

		$this->Purchaser->ContactPerson->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name'];
		$this->paginate['ContactPerson'] = array(
			'conditions' => $contact_people_conditions,
			'contain' => array(),
			'limit' => 30,
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('Purchaser.id = ContactPerson.purchaser_id')
				),
				array(
					'table' => 'addresses',
					'alias' => 'PurchaserAddress',
					'type' => 'LEFT',
					'conditions' => array('Purchaser.id = PurchaserAddress.purchaser_id')
				),
				array(
					'table' => 'addresses',
					'alias' => 'ContactPersonAddress',
					'type' => 'LEFT',
					'conditions' => array('ContactPerson.id = ContactPersonAddress.contact_person_id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'INNER',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => array('User.id = Purchaser.user_id')
				)
			),
			'fields' => array(
				'ContactPerson.*',
					
				'Purchaser.id',
			)
		);
		$contact_people = $this->paginate('ContactPerson');
		unset($this->Purchaser->ContactPerson->virtualFields['purchaser_name']);
		$this->set('contact_people_paging', $this->params['paging']);
		
		$contact_people_find = $this->paginate['ContactPerson'];
		unset($contact_people_find['limit']);
		unset($contact_people_find['fields']);
		$this->set('contact_people_find', $contact_people_find);
		
		$this->Purchaser->ContactPerson->setExportFields();
		$this->set('contact_people_export_fields', $this->Purchaser->ContactPerson->export_fields);
		
		$contact_people_back_link = array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 7) + $this->passedArgs;
		$contact_people_back_link = base64_encode(serialize($contact_people_back_link));
		$this->set('contact_people_back_link', $contact_people_back_link);
		
		// OBCHODNI JEDNANI TOHOTO ODBERATELE
		$business_sessions_conditions[] = 'BusinessSession.purchaser_id = ' . $id;
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'business_sessions') {
			$this->Session->delete('Search.BusinessSessionSearch');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $id, 'tab' => 8));
		}

		// pokud chci vysledky vyhledavani
		if ( isset($this->data['BusinessSessionSearch']['BusinessSession']['search_form']) && $this->data['BusinessSessionSearch']['BusinessSession']['search_form'] == 1 ){
			$this->Session->write('Search.BusinessSessionSearch', $this->data['BusinessSessionSearch']);
			$business_sessions_conditions = $this->Purchaser->BusinessSession->do_form_search($business_sessions_conditions, $this->data['BusinessSessionSearch']);
		} elseif ($this->Session->check('Search.BusinessSessionSearch')) {
			$this->data['BusinessSessionSearch'] = $this->Session->read('Search.BusinessSessionSearch');
			$business_sessions_conditions = $this->Purchaser->BusinessSession->do_form_search($business_sessions_conditions, $this->data['BusinessSessionSearch']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 8) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}

		$this->Purchaser->BusinessSession->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name'];
		$this->paginate['BusinessSession'] = array(
			'conditions' => $business_sessions_conditions,
			'contain' => array(
				'BusinessSessionState',
				'BusinessSessionType',
				'User',
			),
			'order' => array('BusinessSession.date' => 'desc'),
			'fields' => array('*'),
			'limit' => 30,
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'LEFT',
					'conditions' => array('Purchaser.id = BusinessSession.purchaser_id')	
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'LEFT',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
			),
		);
		$business_sessions = $this->paginate('BusinessSession');
		unset($this->Purchaser->BusinessSession->virtualFields['purchaser_name']);
		$this->set('business_sessions_paging', $this->params['paging']);
		$this->set('business_session_types', $this->Purchaser->BusinessSession->BusinessSessionType->find('list'));
		// doplnim, jestli se da obchodni jednani smazat
		foreach ($business_sessions as &$business_session) {
			$business_session['BusinessSession']['is_deletable'] = $this->Purchaser->BusinessSession->isDeletable($business_session['BusinessSession']['id']);
		}
		
		$business_sessions_find = $this->paginate['BusinessSession'];
		unset($business_sessions_find['limit']);
		unset($business_sessions_find['fields']);
		// do vypisu CSV chci i dalsi data
		$business_sessions_find['joins']= array_merge($business_sessions_find['joins'], $this->Purchaser->BusinessSession->export_joins);
		$this->set('business_sessions_find', $business_sessions_find);
		$this->Purchaser->BusinessSession->setExportFields();
		$this->set('business_sessions_export_fields', $this->Purchaser->BusinessSession->export_fields);
		
		$business_sessions_back_link = array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 8) + $this->passedArgs;
		$business_sessions_back_link = base64_encode(serialize($business_sessions_back_link));
		$this->set('business_sessions_back_link', $business_sessions_back_link);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->Purchaser->BusinessSession->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
		
		// DOKUMENTY ODBERATELE
		$documents_conditions = '';

		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'documents') {
			$this->Session->delete('Search.DocumentForm2');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 6));
		}
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['DocumentForm2']['Document']['search_form']) && $this->data['DocumentForm2']['Document']['search_form'] == 1 ){
			$this->Session->write('Search.DocumentForm2', $this->data['DocumentForm2']);
			$documents_conditions = $this->Purchaser->Document->do_form_search($documents_conditions, $this->data['DocumentForm2']);
		} elseif ($this->Session->check('Search.DocumentForm2')) {
			$this->data['DocumentForm2'] = $this->Session->read('Search.DocumentForm2');
			$documents_conditions = $this->Purchaser->Document->do_form_search($documents_conditions, $this->data['DocumentForm2']);
		}
		
		$query = '
		SELECT *
		FROM
			((SELECT Document.*
			FROM
				documents AS Document
			WHERE
				Document.purchaser_id = ' . $id . '
			)
			UNION (
			SELECT Document.*
			FROM documents AS Document, offers AS Offer, business_sessions AS BusinessSession, purchasers AS Purchaser
			WHERE
				Document.offer_id = Offer.id AND
				Offer.business_session_id = BusinessSession.id AND
				Purchaser.id = BusinessSession.purchaser_id AND 
				Purchaser.business_partner_id = ' . $id . '
			)) AS Document
		';
		
		if (!empty($documents_conditions)) {
			$query = $query . 'WHERE ' . $documents_conditions;
		}
		
		$documents = $this->Purchaser->Document->query($query);

		// POLOZKY SKLADU OBCHODNIHO PARTNERA
		$store_items_conditions = array('StoreItem.purchaser_id' => $id);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'store_items') {
			$this->Session->delete('Search.StoreItemForm');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 9));
		}

		// pokud chci vysledky vyhledavani
		if ( isset($this->data['StoreItemForm']['StoreItem']['search_form']) && $this->data['StoreItemForm']['StoreItem']['search_form'] == 1 ){
			$this->Session->write('Search.StoreItemForm', $this->data['StoreItemForm']);
			$store_items_conditions = $this->Purchaser->StoreItem->do_form_search($store_items_conditions, $this->data['StoreItemForm']);
		} elseif ($this->Session->check('Search.StoreItemForm')) {
			$this->data['StoreItemForm'] = $this->Session->read('Search.StoreItemForm');
			$store_items_conditions = $this->Purchaser->StoreItem->do_form_search($store_items_conditions, $this->data['StoreItemForm']);
		}

		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 9) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}
		
		// musim si k StoreItem naimportovat unit, aby fungovalo razeni
		App::import('Model', 'Unit');
		$this->Purchaser->StoreItem->Unit = new Unit;
		
		// chci znat pocet polozek skladu odberatele
		$count = $this->Purchaser->StoreItem->find('count', array(
			'conditions' => $store_items_conditions,
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
			)
		));
		
		$this->Purchaser->StoreItem->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name'];
		// pomoci strankovani (abych je mohl jednoduse radit) vyberu VSECHNY polozky skladu odberatele
		$this->paginate['StoreItem'] = array(
			'conditions' => $store_items_conditions,
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
				'Product.price',
					
				'Unit.shortcut'
			),
			'limit' => $count,
			'order' => array('Product.vzp_code' => 'asc')
		);
		$store_items = $this->paginate('StoreItem');
		unset($this->Purchaser->StoreItem->virtualFields['purchaser_name']);
		
		// budu pocitat celkove soucty polozek a soucet ceny vsech polozek
		$store_items_quantity = 0;
		$store_items_price = 0;
		// k polozkam skladu doplnim datum posledniho prodeje, ve kterem byla polozka obsazena
		foreach ($store_items as &$store_item) {
			$store_items_quantity += $store_item['StoreItem']['quantity'];
			$store_items_price += $store_item['StoreItem']['item_total_price'];
			$store_item['StoreItem']['last_sale_date'] = $this->Purchaser->Sale->getLastDate($id, $store_item['Product']['id']);
		}
		$this->set('store_items_quantity', $store_items_quantity);
		$this->set('store_items_price', $store_items_price);
		
		$this->set('store_items_paging', $this->params['paging']);
		
		$store_items_find = $this->paginate['StoreItem'];
		unset($store_items_find['limit']);
		unset($store_items_find['fields']);
		$this->set('store_items_find', $store_items_find);
		
		$this->set('store_items_export_fields', $this->Purchaser->StoreItem->export_fields);
		
		// DODACI LISTY
		$delivery_notes_conditions = array(
			'DeliveryNote.purchaser_id' => $id,
		);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'delivery_notes') {
			$this->Session->delete('Search.DeliveryNoteForm');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 10));
		}
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['DeliveryNoteForm']['DeliveryNote']['search_form']) && $this->data['DeliveryNoteForm']['DeliveryNote']['search_form'] == 1 ){
			$this->Session->write('Search.DeliveryNoteForm', $this->data['DeliveryNoteForm']);
			$delivery_notes_conditions = $this->Purchaser->DeliveryNote->do_form_search($delivery_notes_conditions, $this->data['DeliveryNoteForm']);
		} elseif ($this->Session->check('Search.DeliveryNoteForm')) {
			$this->data['DeliveryNoteForm'] = $this->Session->read('Search.DeliveryNoteForm');
			$delivery_notes_conditions = $this->Purchaser->DeliveryNote->do_form_search($delivery_notes_conditions, $this->data['DeliveryNoteForm']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 10) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}
		
		// musim si k StoreItem naimportovat unit, aby fungovalo razeni
		App::import('Model', 'Product');
		$this->Purchaser->DeliveryNote->Product = new Product;
		App::import('Model', 'Unit');
		$this->Purchaser->DeliveryNote->Unit = new Unit;
		
		$this->Purchaser->DeliveryNote->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name'];
		$this->paginate['DeliveryNote'] = array(
			'conditions' => $delivery_notes_conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'products_transactions',
					'alias' => 'ProductsTransaction',
					'type' => 'left',
					'conditions' => array('DeliveryNote.id = ProductsTransaction.transaction_id')
				),
				array(
					'table' => 'products',
					'alias' => 'Product',
					'type' => 'left',
					'conditions' => array('ProductsTransaction.product_id = Product.id')
				),
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('DeliveryNote.purchaser_id = Purchaser.id')
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
					'conditions' => array('Address.purchaser_id = Purchaser.id')
				),
				array(
					'table' => 'units',
					'alias' => 'Unit',
					'type' => 'left',
					'conditions' => array('Product.unit_id = Unit.id')
				),
				array(
					'table' => 'transaction_types',
					'alias' => 'TransactionType',
					'type' => 'LEFT',
					'conditions' => array('DeliveryNote.transaction_type_id = TransactionType.id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'left',
					'conditions' => array('DeliveryNote.user_id = User.id')
				)
			),
			'fields' => array(
				'DeliveryNote.id',
				'DeliveryNote.date',
				'DeliveryNote.code',
				'DeliveryNote.transaction_type_id',
				'DeliveryNote.abs_quantity',
				'DeliveryNote.abs_total_price',
				'DeliveryNote.total_price',
				'DeliveryNote.quantity',
				'DeliveryNote.purchaser_name',
		
				'ProductsTransaction.id',
				'ProductsTransaction.quantity',
				'ProductsTransaction.unit_price',
		
				'Product.id',
				'Product.name',
				'Product.vzp_code',
				'Product.group_code',
					
				'Purchaser.id',
				'Purchaser.degree_before',
				'Purchaser.degree_after',
					
				'BusinessPartner.id',
				'BusinessPartner.name',
					
				'Unit.id',
				'Unit.shortcut',
					
				'TransactionType.id',
				'TransactionType.subtract',
			),
			'order' => array(
				'DeliveryNote.created' => 'desc'
			)
		);
		$delivery_notes = $this->paginate('DeliveryNote');
		unset($this->Purchaser->DeliveryNote->virtualFields['purchaser_name']);

		$this->set('delivery_notes_paging', $this->params['paging']);
		
		$delivery_notes_find = $this->paginate['DeliveryNote'];
		unset($delivery_notes_find['limit']);
		unset($delivery_notes_find['fields']);
		$delivery_notes_find = $this->Purchaser->DeliveryNote->getCSVFind($delivery_notes_find);
		$this->set('delivery_notes_find', $delivery_notes_find);
		
		$delivery_notes_export_fields = $this->Purchaser->DeliveryNote->export_fields();
		$this->set('delivery_notes_export_fields', $delivery_notes_export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$delivery_notes_users_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$delivery_notes_users_conditions = array('User.id' => $this->user['User']['id']);
		}
		$delivery_notes_users = $this->Purchaser->DeliveryNote->User->find('all', array(
			'conditions' => $delivery_notes_users_conditions,
			'contain' => array(),
			'fields' => array('User.id', 'User.first_name', 'User.last_name')
		));
		$delivery_notes_users = Set::combine($delivery_notes_users, '{n}.User.id', array('{0} {1}', '{n}.User.first_name', '{n}.User.last_name'));
		$this->set('delivery_notes_users', $delivery_notes_users);
		
		// PRODEJE
		$sales_conditions = array(
			'Sale.purchaser_id' => $id,
		);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'sales') {
			$this->Session->delete('Search.SaleForm');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 11));
		}

		// pokud chci vysledky vyhledavani
		if ( isset($this->data['SaleForm']['Sale']['search_form']) && $this->data['SaleForm']['Sale']['search_form'] == 1 ){
			$this->Session->write('Search.SaleForm', $this->data['SaleForm']);
			$sales_conditions = $this->Purchaser->Sale->do_form_search($sales_conditions, $this->data['SaleForm']);
		} elseif ($this->Session->check('Search.SaleForm')) {
			$this->data['SaleForm'] = $this->Session->read('Search.SaleForm');
			$sales_conditions = $this->Purchaser->Sale->do_form_search($sales_conditions, $this->data['SaleForm']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 11) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}
		
		// musim si k StoreItem naimportovat unit, aby fungovalo razeni
		App::import('Model', 'Product');
		$this->Purchaser->Sale->Product = new Product;
		App::import('Model', 'Unit');
		$this->Purchaser->Sale->Unit = new Unit;
		
		$this->Purchaser->Sale->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name'];
		$this->paginate['Sale'] = array(
			'conditions' => $sales_conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'products_transactions',
					'alias' => 'ProductsTransaction',
					'type' => 'left',
					'conditions' => array('Sale.id = ProductsTransaction.transaction_id')
				),
				array(
					'table' => 'products',
					'alias' => 'Product',
					'type' => 'left',
					'conditions' => array('ProductsTransaction.product_id = Product.id')
				),
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('Sale.purchaser_id = Purchaser.id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'left',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
				array(
					'table' => 'units',
					'alias' => 'Unit',
					'type' => 'left',
					'conditions' => array('Product.unit_id = Unit.id')
				),
				array(
					'table' => 'transaction_types',
					'alias' => 'TransactionType',
					'type' => 'LEFT',
					'conditions' => array('Sale.transaction_type_id = TransactionType.id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'left',
					'conditions' => array('Sale.user_id = User.id')
				)
			),
			'fields' => array(
				'Sale.id',
				'Sale.date',
				'Sale.code',
				'Sale.abs_quantity',
				'Sale.abs_total_price',
				'Sale.purchaser_name',
		
				'ProductsTransaction.id',
				'ProductsTransaction.unit_price',
		
				'Product.id',
				'Product.name',
				'Product.vzp_code',
				'Product.group_code',
					
				'Purchaser.id',
				'Purchaser.degree_before',
				'Purchaser.degree_after',
					
				'BusinessPartner.id',
				'BusinessPartner.name',
					
				'Unit.id',
				'Unit.shortcut',
					
				'TransactionType.id',
				'TransactionType.subtract',
			),
			'order' => array(
				'Sale.created' => 'desc',
			)
		);
		$sales = $this->paginate('Sale');
		unset($this->Purchaser->Sale->virtualFields['purchaser_name']);

		$this->set('sales_paging', $this->params['paging']);
		
		$sales_find = $this->paginate['Sale'];
		unset($sales_find['limit']);
		unset($sales_find['fields']);
		$sales_find = $this->Purchaser->Sale->getCSVFind($sales_find);
		$this->set('sales_find', $sales_find);
		
		$sales_export_fields = $this->Purchaser->Sale->export_fields();
		$this->set('sales_export_fields', $sales_export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$sales_users_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$sales_users_conditions = array('User.id' => $this->user['User']['id']);
		}
		$sales_users = $this->Purchaser->Sale->User->find('all', array(
			'conditions' => $sales_users_conditions,
			'contain' => array(),
			'fields' => array('User.id', 'User.first_name', 'User.last_name')
		));
		$sales_users = Set::combine($sales_users, '{n}.User.id', array('{0} {1}', '{n}.User.first_name', '{n}.User.last_name'));
		$this->set('sales_users', $sales_users);
		
		// POHYBY
		$transactions_conditions = array(
			'Transaction.purchaser_id' => $id,
		);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'transactions') {
			$this->Session->delete('Search.TransactionForm');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 12));
		}
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['TransactionForm']['Transaction']['search_form']) && $this->data['TransactionForm']['Transaction']['search_form'] == 1 ){
			$this->Session->write('Search.TransactionForm', $this->data['TransactionForm']);
			$transactions_conditions = $this->Purchaser->Transaction->do_form_search($transactions_conditions, $this->data['TransactionForm']);
		} elseif ($this->Session->check('Search.TransactionForm')) {
			$this->data['TransactionForm'] = $this->Session->read('Search.TransactionForm');
			$transactions_conditions = $this->Purchaser->Transaction->do_form_search($transactions_conditions, $this->data['TransactionForm']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 12) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}
		
		// musim si k StoreItem naimportovat unit, aby fungovalo razeni
		App::import('Model', 'Product');
		$this->Purchaser->Transaction->Product = new Product;
		App::import('Model', 'Unit');
		$this->Purchaser->Transaction->Unit = new Unit;
	
		$this->Purchaser->Transaction->virtualFields['purchaser_name'] = $this->Purchaser->virtualFields['name']; 
		$this->paginate['Transaction'] = array(
			'conditions' => $transactions_conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'products_transactions',
					'alias' => 'ProductsTransaction',
					'type' => 'left',
					'conditions' => array('Transaction.id = ProductsTransaction.transaction_id')
				),
				array(
					'table' => 'products',
					'alias' => 'Product',
					'type' => 'left',
					'conditions' => array('ProductsTransaction.product_id = Product.id')
				),
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('Transaction.purchaser_id = Purchaser.id')
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'left',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				),
				array(
					'table' => 'units',
					'alias' => 'Unit',
					'type' => 'left',
					'conditions' => array('Product.unit_id = Unit.id')
				),
				array(
					'table' => 'transaction_types',
					'alias' => 'TransactionType',
					'type' => 'LEFT',
					'conditions' => array('Transaction.transaction_type_id = TransactionType.id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'left',
					'conditions' => array('Transaction.user_id = User.id')
				)
			),
			'fields' => array(
				'Transaction.id',
				'Transaction.date',
				'Transaction.code',
				'Transaction.quantity',
				'Transaction.total_price',
				'Transaction.purchaser_name',
		
				'ProductsTransaction.id',
				'ProductsTransaction.unit_price',
		
				'Product.id',
				'Product.name',
				'Product.vzp_code',
				'Product.group_code',
					
				'Purchaser.id',
				'Purchaser.degree_before',
				'Purchaser.degree_after',
					
				'BusinessPartner.id',
				'BusinessPartner.name',
					
				'Unit.id',
				'Unit.shortcut',
					
				'TransactionType.id',
				'TransactionType.subtract'
			),
			'order' => array(
				'Transaction.date' => 'desc',
				'Transaction.time' => 'desc'
			)
		);
		$transactions = $this->paginate('Transaction');
		unset($this->Purchaser->Transaction->virtualFields['purchaser_name']);

		$this->set('transactions_paging', $this->params['paging']);
		
		$transactions_find = $this->paginate['Transaction'];
		unset($transactions_find['limit']);
		unset($transactions_find['fields']);
		$transactions_find = $this->Purchaser->Transaction->getCSVFind($transactions_find);
		$this->set('transactions_find', $transactions_find);
		
		$transactions_export_fields = $this->Purchaser->Transaction->export_fields();
		$this->set('transactions_export_fields', $transactions_export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$transactions_users_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$transactions_users_conditions = array('User.id' => $this->user['User']['id']);
		}
		$transactions_users = $this->Purchaser->Transaction->User->find('all', array(
			'conditions' => $transactions_users_conditions,
			'contain' => array(),
			'fields' => array('User.id', 'User.first_name', 'User.last_name')
		));
		$transactions_users = Set::combine($transactions_users, '{n}.User.id', array('{0} {1}', '{n}.User.first_name', '{n}.User.last_name'));
		$this->set('transactions_users', $transactions_users);
		
		// KOREKCE PENEZENKY
		if ($this->Acl->check(array('model' => 'User', 'foreign_key' => $this->Session->read('Auth.User.id')), 'controllers/Purchasers/user_wallet_correction')) {
			$wallet_corrections_conditions = array(
				'WalletTransaction.purchaser_id' => $id,
				'WalletTransaction.sale_id' => 0,
				'WalletTransaction.contract_id' => 0
			);
			
			unset($this->passedArgs['sort']);
			unset($this->passedArgs['direction']);
			if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 13) {
				$this->passedArgs['sort'] = $sort_field;
				$this->passedArgs['direction'] = $sort_direction;
			}
			
			$this->paginate['WalletTransaction'] = array(
				'conditions' => $wallet_corrections_conditions,
				'limit' => 30,
				'contain' => array('User'),
				'fields' => array('WalletTransaction.*'),
				'order' => array(
					'WalletTransaction.created' => 'desc'
				)
			);
			$this->Purchaser->WalletTransaction->virtualFields['user_name'] = $this->Purchaser->WalletTransaction->User->full_name;
			$wallet_transactions = $this->paginate('WalletTransaction');
			$this->set('wallet_transactions_virtual_fields', $this->Purchaser->WalletTransaction->virtualFields);
			unset($this->Purchaser->WalletTransaction->virtualFields['user_name']);
			$this->set('wallet_transactions_paging', $this->params['paging']);
			$wallet_transactions_find = $this->paginate['WalletTransaction'];
			unset($wallet_transactions_find['limit']);
			unset($wallet_transactions_find['fields']);
			$this->set('wallet_transactions_find', $wallet_transactions_find);
				
			$wallet_transactions_export_fields = $this->Purchaser->WalletTransaction->export_fields;
			$this->set('wallet_transactions_export_fields', $wallet_transactions_export_fields);
			
			$this->set('wallet_transactions', $wallet_transactions);
		}
		
		$this->set('purchaser', $purchaser);
		$this->set('contact_people', $contact_people);
		$this->set('business_sessions', $business_sessions);
		$this->set('documents', $documents);
		$this->set('store_items', $store_items);
		$this->set('delivery_notes', $delivery_notes);
		$this->set('sales', $sales);
		$this->set('transactions', $transactions);
		
		$this->set('user', $this->user);
	}
	
	function user_add() {
		$user_id = $this->user['User']['id'];
		$this->set('user_id', $user_id);
		
		$redirect = array('controller' => 'purchasers', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'view', $this->params['named']['business_partner_id'], 'tab' => 3);
		}
		
		if (isset($this->data)) {
			// dogeneruju si nazev obchodniho partnera, pokud nemam zadany
			if (empty($this->data['Purchaser']['name']) && (!empty($this->data['Purchaser']['first_name']) || !empty($this->data['Purchaser']['last_name']))) {
				$name = array(
					$this->data['Purchaser']['degree_before'],
					$this->data['Purchaser']['first_name'],
					$this->data['Purchaser']['last_name'],
					$this->data['Purchaser']['degree_after']
				);
				$name = array_filter($name);
				$name = implode(' ', $name);
				$this->data['Purchaser']['name'] = $name;
			}
			
			if ($this->Purchaser->saveAll($this->data)) {
				$this->Session->setFlash('Odběratel byl uložen');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Odběratele se nepodařilo uložit, opravte chyby ve formuláři a uložte jej prosím znovu');
			}
		} else {
			$this->data['Purchaser']['bonity'] = 5;
			if (isset($this->params['named']['business_partner_id'])) {
				$this->data['Purchaser']['business_partner_id'] = $this->params['named']['business_partner_id'];
				$this->data['Purchaser']['business_partner_name'] = $this->Purchaser->BusinessPartner->autocomplete_field_info($this->params['named']['business_partner_id']);
			}
			$this->data['Purchaser']['business_partner_address'] = false;
		}
		
		$purchaser_types = $this->Purchaser->PurchaserType->find('list', array(
			'conditions' => array('PurchaserType.active' => true),
			'contain' => array(),
			'order' => 'FIELD(PurchaserType.id, 1, 4, 5)'
		));
		$this->set('purchaser_types', $purchaser_types);
		
		$education_types = $this->Purchaser->EducationType->find('list', array(
			'conditions' => array('EducationType.active' => true),
			'contain' => array(),
			'order' => array('EducationType.name' => 'asc')
		));
		$this->set('education_types', $education_types);
	}
	
	function user_edit($id = null) {
		$redirect = $this->index_link;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		}

		if (!isset($id)) {
			$this->Session->setFlash('Není zadán odběratel, kterého chcete upravit');
			$this->redirect($redirect);
		}
		
		$purchaser = $this->Purchaser->find('first', array(
			'conditions' => array('Purchaser.id' => $id),
			'contain' => array('Address')
		));
		
		if (empty($purchaser)) {
			$this->Session->setFlash('Odběratel, kterého chcete upravit, neexistuje');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'index'));
		}

		// user muze upravovat pouze sve odberatele
		if (!$this->Purchaser->checkUser($this->user, $purchaser['Purchaser']['user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo zobrazit tento úkol.');
			$this->redirect($this->index_link);
		}

		if (isset($this->data)) {
			if ($this->Purchaser->saveAll($this->data)) {
				$this->Session->setFlash('Odběratel byl uložen');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Odběratele se nepodařilo uložit, opravte chyby ve formuláři a uložte jej prosím znovu');
			}
		} else {
			$this->data = $purchaser;
			$this->data['Purchaser']['business_partner_name'] = $this->Purchaser->BusinessPartner->autocomplete_field_info($purchaser['Purchaser']['business_partner_id']);
		}
		
		$purchaser_types = $this->Purchaser->PurchaserType->find('list', array(
			'conditions' => array('PurchaserType.active' => true),
			'contain' => array(),
			'order' => 'FIELD(PurchaserType.id, 1, 4, 5)'
		));
		$this->set('purchaser_types', $purchaser_types);
		
		$education_types = $this->Purchaser->EducationType->find('list', array(
			'conditions' => array('EducationType.active' => true),
			'contain' => array(),
			'order' => array('EducationType.name' => 'asc')
		));
		$this->set('education_types', $education_types);
	}
	
	function user_edit_user($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určen odběratel, kterého chcete upravovat');
			$this->redirect($this->index_link);
		}
	
		$purchaser = $this->Purchaser->find('first', array(
			'conditions' => array('Purchaser.id' => $id),
			'contain' => array(
				'User' => array(
					'fields' => array('User.id', 'CONCAT(User.last_name, " ", User.first_name) as full_name')
				)
			)
		));
	
		if (empty($purchaser)) {
			$this->Session->setFlash('Zvolený odběratel neexistuje');
			$this->redirect($this->index_link);
		}
	
		$this->set('purchaser', $purchaser);
	
		$users = $this->Purchaser->User->find('all', array(
			'fields' => array('User.id', 'CONCAT(User.last_name, " ", User.first_name) as full_name'),
			'order' => array('full_name' => 'asc'),
			'contain' => array()
		));
	
		$autocomplete_users = array();
		foreach ($users as $key => $user) {
			$autocomplete_users[] = array('label' => $user[0]['full_name'], 'value' => $user['User']['id']);
		}
	
		$this->set('users', json_encode($autocomplete_users));
	
		if (isset($this->data)) {
			// zmenim uzivatele u obchodniho partnera
			if ($this->Purchaser->save($this->data)) {
				// a taky u obchodnich jednani daneho partnera
				$business_sessions = $this->Purchaser->BusinessSession->find('all', array(
					'conditions' => array('BusinessSession.purchaser_id' => $purchaser['Purchaser']['id']),
					'contain' => array(),
					'fields' => array('id')
				));
	
				foreach ($business_sessions as $business_session) {
					$business_session['BusinessSession']['user_id'] = $this->data['BusinessPartner']['user_id'];
					$this->Purchaser->BusinessSession->save($business_session);
				}
	
				// a u ukolu k danemu obchodnimu partnerovi
				$impositions = $this->Purchaser->Imposition->find('all', array(
					'conditions' => array('Imposition.purchaser_id' => $purchaser['Purchaser']['id']),
					'contain' => array(),
					'fields' => array('id')
				));
	
				foreach ($impositions as $imposition) {
					$imposition['Imposition']['user_id'] = $this->data['Purchaser']['user_id'];
					$this->Purchaser->Imposition->save($imposition);
				}
	
				$this->Session->setFlash('Uživatel zodpovědný za odběratele byl upraven.');
				$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id']));
			} else {
				$this->Session->setFlash('Uživatele se nepodařilo upravit, opakujte prosím akci.');
			}
		} else {
			$this->data['Purchaser']['user_name'] = $purchaser[0]['full_name'];
		}
	}
	
	function user_delete($id = null) {
		$redirect = $this->index_link;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		}
		
		if (!isset($id)) {
			$this->Session->setFlash('Není zadán odběratel, kterého chcete smazat');
			$this->redirect($redirect);
		}
		
		$purchaser = $this->Purchaser->find('first', array(
			'conditions' => array(
				'Purchaser.id' => $id,
				'Purchaser.active' => true
			),
			'contain' => array(),
			'fields' => array('Purchaser.id', 'Purchaser.user_id')
		));
		
		if (empty($purchaser)) {
			$this->Session->setFlash('Odběratel, kterého chcete smazat, neexistuje');
			$this->redirect($redirect);
		}
		
		// user muze upravovat pouze sve odberatele
		if (!$this->Purchaser->checkUser($this->user, $purchaser['Purchaser']['user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo zobrazit tento úkol.');
			$this->redirect($redirect);
		}
		
		if ($this->Purchaser->delete($purchaser['Purchaser']['id'])) {
			$this->Session->setFlash('Odběratel byl odstraněn');
		} else {
			$this->Session->setFlash('Odběratele se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect($redirect);
	}
	
	function user_recount_all_wallets() {
		$this->Purchaser->recountAllWallets();
		die('hotovo');
	}
	
	function user_recount_wallet($id = null) {
		return $this->Purchaser->recountWallet($id);
		die('hotovo');
	}
	
	function user_autocomplete_list() {
		$term = null;
		if ($_GET['term']) {
			$term = $_GET['term'];
		}
		
		echo $this->Purchaser->autocomplete_list($this->user, $term);
		die();
	}
	
	function user_wallet_correction($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadán odběratel');
			$this->redirect(array('controller' => 'purchasers', 'action' => 'index'));
		}

		$purchaser = $this->Purchaser->find('first', array(
			'conditions' => array('Purchaser.id' => $id),
			'contain' => array(),
		));
		
		if (isset($this->data)) {
			$this->Purchaser->set($this->data);
			if ($this->Purchaser->validates()) {
				$user = $this->Auth->user();
				$dataSource = $this->Purchaser->getDataSource();
				$dataSource->begin($this->Purchaser);
				if ($this->Purchaser->wallet_transaction($id, $this->data['Purchaser']['wallet_correction'], 'correction', 0, $user['User']['id'])) {
					$dataSource->commit($this->Purchaser);
					$this->Session->setFlash('Korekce byla uložena');
					$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $id));
				} else {
					$dataSource->rollback($this->Purchaser);
					$this->Session->setFlash('Korekci se nepodařilo uložit');
				}
			} else {
				$this->Session->setFlash('Korekci se nepodařilo uložit, opravte chyby ve formuláři.');
			}
		} else {
			$this->data = $purchaser;
		}
		$this->set('purchaser', $purchaser);
	}
	
	function store_items($id = null) {
		$res = array(
			'success' => false,
			'message' => null
		);
		
		if (!$id) {
			$res['message'] = 'Není zadán odběratel, jehož sklad chcete vypsat';
		} else {
			$this->Purchaser->StoreItem->virtualFields = array();
			$store_items = $this->Purchaser->StoreItem->find('all', array(
				'conditions' => array(
					'StoreItem.purchaser_id' => $id,
//					'StoreItem.quantity !=' => 0
				),
				'contain' => array(
					'Product'
				),
				'order' => array('Product.name' => 'asc')
			));
			
			foreach ($store_items as &$store_item) {
				$store_item['StoreItem']['week_reserve'] = $this->Purchaser->StoreItem->getWeekReserve($store_item['StoreItem']['id']);
				$store_item['StoreItem']['last_sale_date'] = $this->Purchaser->Sale->getLastDate($id, $store_item['StoreItem']['product_id'], true);
			}

			$res['success'] = true;
			$res['data'] = $store_items;
		}
		
		echo json_encode($res); die();
	}

}
?>