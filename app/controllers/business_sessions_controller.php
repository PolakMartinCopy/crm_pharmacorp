<?php
class BusinessSessionsController extends AppController {
	var $name = 'BusinessSessions';
	
	var $index_link = array('controller' => 'business_sessions', 'action' => 'index');
	
	var $left_menu_list = array('business_sessions');

	function beforeFilter(){
		parent::beforeFilter();
		$this->set('active_tab', 'business_sessions');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
		
		$business_session_types = $this->BusinessSession->BusinessSessionType->find('list');
		$this->set('business_session_types', $business_session_types);
		
		$this->set('user', $this->user);
	}
	
	function user_index() {
		$user_id = $this->user['User']['id'];
		
		$conditions = array('Purchaser.active = 1', 'BusinessPartner.active = 1');
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'business_sessions') {
			$this->Session->delete('Search.BusinessSessionSearch');
			$this->redirect(array('controller' => 'business_sessions', 'action' => 'index'));
		}

		// pokud chci vysledky vyhledavani
		if ( isset($this->data['BusinessSessionSearch']['BusinessSession']['search_form']) && $this->data['BusinessSessionSearch']['BusinessSession']['search_form'] == 1 ){
			$this->Session->write('Search.BusinessSessionSearch', $this->data['BusinessSessionSearch']);
			$conditions = $this->BusinessSession->do_form_search($conditions, $this->data['BusinessSessionSearch']);
		} elseif ($this->Session->check('Search.BusinessSessionSearch')) {
			$this->data['BusinessSessionSearch'] = $this->Session->read('Search.BusinessSessionSearch');
			$conditions = $this->BusinessSession->do_form_search($conditions, $this->data['BusinessSessionSearch']);
		}
	
		$order = array('BusinessSession.date' => 'desc');
		if (isset($this->params['named']['sort']) && $this->params['named']['sort'] == 'celkem') {
			$order = array($this->params['named']['sort'] => $this->params['named']['direction']);
			unset($this->params['named']['sort']);
			unset($this->params['named']['direction']);
		}
		
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions[] = '(BusinessSession.admin_user_id = ' . $user_id . ' OR BusinessSessionsUser.user_id = ' . $user_id . ')';
		}

		$this->BusinessSession->virtualFields['purchaser_name'] = $this->BusinessSession->Purchaser->virtualFields['name'];
		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array(
				'BusinessSessionState',
				'BusinessSessionType',
				'User',
			),
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
			'order' => $order,
			'fields' => array('*'),
			'limit' => 30,
		);
		$business_sessions = $this->paginate();
		unset($this->BusinessSession->virtualFields['purchaser_name']);
		// doplnim, jestli se da obchodni jednani smazat
		foreach ($business_sessions as &$business_session) {
			$business_session['BusinessSession']['is_deletable'] = $this->BusinessSession->isDeletable($business_session['BusinessSession']['id']);
		}
		$this->set('business_sessions', $business_sessions);

		$find = $this->paginate;
		// do vypisu CSV chci i dalsi data
		$find['joins']= array_merge($find['joins'], $this->BusinessSession->export_joins);
		unset($find['limit']);
		unset($find['fields']);

		$this->set('find', $find);
		
		$this->set('export_fields', $this->BusinessSession->export_fields);
		
		$this->set('user', $this->user);
		
		$back_link = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		$back_link = base64_encode(serialize($back_link));
		$this->set('back_link', $back_link);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->BusinessSession->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
	}
	
	function user_view($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, které chcete zobrazit');
			$this->redirect(array('controller' => 'business_sessions', 'action' => 'index'));
		}
		
		$sort_field = '';
		if (isset($this->passedArgs['sort'])) {
			$sort_field = $this->passedArgs['sort'];
		}
		
		$sort_direction = '';
		if (isset($this->passedArgs['direction'])) {
			$sort_direction = $this->passedArgs['direction'];
		}
		
		$costs_conditions = array();
	
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'business_sessions_costs') {
			$this->Session->delete('Search.BusinessSessionsCostForm');
			$this->redirect(array('controller' => 'business_sessions', 'action' => 'view', $id, 'tab' => 3));
		} 

		// pokud chci vysledky vyhledavani v nakladech
		if ( isset($this->data['BusinessSessionsCostForm']['BusinessSessionsCost']['search_form']) && $this->data['BusinessSessionsCostForm']['BusinessSessionsCost']['search_form'] == 1 ){
			$this->Session->write('Search.BusinessSessionsCostForm', $this->data['BusinessSessionsCostForm']);
			$costs_conditions = $this->BusinessSession->BusinessSessionsCost->do_form_search($costs_conditions, $this->data['BusinessSessionsCostForm']);
		} elseif ($this->Session->check('Search.BusinessSessionsCostForm')) {
			$this->data['BusinessSessionsCostForm'] = $this->Session->read('Search.BusinessSessionsCostForm');
			$costs_conditions = $this->BusinessSession->BusinessSessionsCost->do_form_search($costs_conditions, $this->data['BusinessSessionsCostForm']);
		}

		$this->BusinessSession->virtualFields['purchaser_name'] = $this->BusinessSession->Purchaser->virtualFields['name'];
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id, 'Purchaser.active' => true, 'BusinessPartner.active' => true),
			'contain' => array(
				'User',
				'BusinessSessionState',
				'BusinessSessionType',
				'BusinessSessionsCost' => array(
					'conditions' => $costs_conditions,
					'CostType'
				)
			),
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
			'fields' => array('*')
		));
		unset($this->BusinessSession->virtual_fields['purchaser_name']);
		
		if (empty($business_session)) {
			$this->Session->setFlash('Zvolené obchodní jednání neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo zobrazit informace o tomto obchodním jednání.');
			$this->redirect($this->index_link);
		}
		
		$this->set('business_session', $business_session);
		
		$costs_conditions['BusinessSession.id'] = $id;
		$costs_find = array(
			'conditions' => $costs_conditions,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'business_sessions',
					'alias' => 'BusinessSession',
					'type' => 'INNER',
					'conditions' => array(
						'BusinessSessionsCost.business_session_id = BusinessSession.id'
					)
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'INNER',
					'conditions' => array(
						'Purchaser.id = BusinessSession.purchaser_id'
					)
				)
			)
		);
		$this->set('costs_find', $costs_find);
		$this->set('costs_export_fields', $this->BusinessSession->BusinessSessionsCost->export_fields);
		
		// DOHODY NA TOMTO OBCHODNIM JEDNANI
		$contracts_conditions = array(
			'Contract.business_session_id' => $id,
		);
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'contracts') {
			$this->Session->delete('Search.ContractForm');
			$this->redirect(array('controller' => 'business_sessions', 'action' => 'view', $id, 'tab' => 2));
		}
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['ContractForm']['Contract']['search_form']) && $this->data['ContractForm']['Contract']['search_form'] == 1) {
			$this->Session->write('Search.ContractForm', $this->data['ContractForm']);
			$contracts_conditions = $this->BusinessSession->Contract->do_form_search($contracts_conditions, $this->data['ContractForm']);
		} elseif ($this->Session->check('Search.ContractForm')) {
			$this->data['ContractForm'] = $this->Session->read('Search.ContractForm');
			$contracts_conditions = $this->BusinessSession->Contract->do_form_search($contracts_conditions, $this->data['ContractForm']);
		}
		
		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 2) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}

		$this->BusinessSession->Contract->virtualFields['contact_person_name'] = $this->BusinessSession->Contract->ContactPerson->virtualFields['name'];
		$this->paginate['Contract'] = array(
			'conditions' => $contracts_conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'contact_people',
					'alias' => 'ContactPerson',
					'type' => 'INNER',
					'conditions' => array('ContactPerson.id = Contract.contact_person_id')
				),					
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => array('Purchaser.id = ContactPerson.purchaser_id')	
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
					'conditions' => array('Address.contact_person_id = ContactPerson.id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'left',
					'conditions' => array('Contract.user_id = User.id')
				)
			),
			'fields' => array(
				'Contract.*',

				'User.id',
				'User.last_name',
			),
			'order' => array(
				'Contract.created' => 'desc',
			)
		);
		$contracts = $this->paginate('Contract');

		unset($this->BusinessSession->Contract->virtualFields['contact_person_name']);
		$this->set('contracts', $contracts);
		$this->set('contract_paging', $this->params['paging']);
		
		$contract_find = $this->paginate['Contract'];
		unset($contract_find['limit']);
		unset($contract_find['fields']);
		$this->set('contract_find', $contract_find);
		
		$this->set('contract_export_fields', $this->BusinessSession->Contract->export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$users_conditions = array('User.id' => $this->user['User']['id']);
		}
		$users = $this->BusinessSession->Contract->User->find('all', array(
			'conditions' => $users_conditions,
			'contain' => array(),
			'fields' => array('User.id', 'User.first_name', 'User.last_name')
		));
		$users = Set::combine($users, '{n}.User.id', array('{0} {1}', '{n}.User.first_name', '{n}.User.last_name'));
		$this->set('users', $users);
		
		$contract_back_link = array('controller' => 'business_sessions', 'action' => 'view', $id, 'tab' => 2) + $this->passedArgs;
		$contract_back_link = base64_encode(serialize($contract_back_link));
		$this->set('contract_back_link', $contract_back_link);
		
		// do leveho menu pridam polozku pro detaily partnera
		$this->left_menu_list[] = 'business_session_detailed';
	}
	
	function user_search() {
		$this->set('monthNames', $this->monthNames);
		$this->set('user', $this->user);
		$this->set('business_session_states', $this->BusinessSession->BusinessSessionState->find('list'));
		
		//$this->data['BusinessSession']['from']['checked'] = false;
		if (isset($this->params['named']['BusinessSession.from.date'])) {
			$this->data['BusinessSession']['from']['checked'] = true;
			$this->data['BusinessSession']['from']['date'] = $this->BusinessSession->unbuilt_date($this->params['named']['BusinessSession.from.date']);
		}
		//$this->data['BusinessSession']['to']['checked'] = false;
		if (isset($this->params['named']['BusinessSession.to.date'])) {
			$this->data['BusinessSession']['to']['checked'] = true;
			$this->data['BusinessSession']['to']['date'] = $this->BusinessSession->unbuilt_date($this->params['named']['BusinessSession.to.date']);
		}
		if (isset($this->params['named']['BusinessPartner.name'])) {
			$this->data['BusinessPartner']['name'] = $this->params['named']['BusinessPartner.name'];
		}
		if (isset($this->params['named']['ContactPerson.name'])) {
			$this->data['ContactPerson']['name'] = $this->params['named']['ContactPerson.name'];
		}
		if (isset($this->params['named']['BusinessSession.business_session_type_id'])) {
			$this->data['BusinessSession']['business_session_type_id'] = $this->params['named']['BusinessSession.business_session_type_id'];
		}
		if (isset($this->params['named']['BusinessSession.business_session_state_id'])) {
			$this->data['BusinessSession']['business_session_state_id'] = $this->params['named']['BusinessSession.business_session_state_id'];
		}
		if (isset($this->params['named']['Address.city'])) {
			$this->data['Address']['city'] = $this->params['named']['Address.city'];
		}
		if (isset($this->params['named']['BusinessPartner.ico'])) {
			$this->data['BusinessPartner']['ico'] = $this->params['named']['BusinessPartner.ico'];
		}
		if (isset($this->params['named']['BusinessSession.description_query'])) {
			$this->data['BusinessSession']['description_query'] = $this->params['named']['BusinessSession.description_query'];
		}
		
		if (isset($this->data)) {
			$conditions = array();
			if (isset($this->data['BusinessSession']['from']['checked']) && isset($this->data['BusinessSession']['to']['checked']) && $this->data['BusinessSession']['from']['checked'] && $this->data['BusinessSession']['to']['checked']) {
				$conditions[] = 'BusinessSession.date BETWEEN \'' . $this->BusinessSession->built_date($this->data['BusinessSession']['from']['date']) . ' 00:00:00\' AND \'' . $this->BusinessSession->built_date($this->data['BusinessSession']['to']['date']) . ' 00:00:00\'';
			} elseif (isset($this->data['BusinessSession']['from']['checked']) && $this->data['BusinessSession']['from']['checked']) {
				$conditions[] = 'BusinessSession.date > \'' . $this->BusinessSession->built_date($this->data['BusinessSession']['from']['date']) . ' 00:00:00\'';
			} elseif (isset($this->data['BusinessSession']['to']['checked']) && $this->data['BusinessSession']['to']['checked']) {
				$conditions[] = 'BusinessSession.date < \'' . $this->BusinessSession->built_date($this->data['BusinessSession']['to']['date']) . ' 00:00:00\'';
			}

			$conditions['BusinessSession.business_session_state_id'] = $this->data['BusinessSession']['business_session_state_id'];
			$conditions['BusinessSession.business_session_type_id'] = $this->data['BusinessSession']['business_session_type_id'];
			if (!empty($this->data['BusinessSession']['description_query'])) {
				$conditions[] = 'BusinessSession.description LIKE \'%%' . $this->data['BusinessSession']['description_query'] . '%%\'';
			}
			if (!empty($this->data['BusinessPartner']['name'])) {
				$conditions[] = 'BusinessPartner.name LIKE \'%%' . $this->data['BusinessPartner']['name'] . '%%\'';
			}
			if (!empty($this->data['BusinessPartner']['ico'])) {
				$conditions[] = 'BusinessPartner.name LIKE \'%%' . $this->data['BusinessPartner']['ico'] . '%%\'';
			}
			if (!empty($this->data['ContactPerson']['name'])) {
				$conditions[] = '(ContactPerson.first_name LIKE \'%%' . $this->data['ContactPerson']['name'] . '%%\' OR ContactPerson.last_name LIKE \'%%' . $this->data['ContactPerson']['name'] . '%%\')';
			}
			if (!empty($this->data['Address']['city'])) {
				$conditions[] = 'Address.city LIKE \'%%' . $this->data['Address']['city'] . '%%\'';
			}
			
			$joins = array(
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'LEFT',
					'conditions' => array(
						'BusinessSession.business_partner_id = BusinessPartner.id'
					)
				),
				array(
					'table' => 'addresses',
					'alias' => 'Address',
					'type' => 'INNER',
					'conditions' => array(
						'BusinessPartner.id = Address.business_partner_id',
						'Address.address_type_id = 1'
					)
				),
				array(
					'table' => 'costs',
					'alias' => 'Cost',
					'type' => 'LEFT',
					'conditions' => array(
						'Cost.business_session_id = BusinessSession.id'
					)
				)
			);
			
			if (isset($this->data['ContactPerson']['name'])) {
				$contact_joins = array(
					array(
						'table' => 'business_sessions_contact_people',
						'alias' => 'BusinessSessionsContactPerson',
						'type' => 'RIGHT',
						'conditions' => array(
							'BusinessSession.id = BusinessSessionsContactPerson.business_session_id'
						)
					),
					array(
						'table' => 'contact_people',
						'alias' => 'ContactPerson',
						'type' => 'LEFT',
						'conditions' => array(
							'BusinessSessionsContactPerson.contact_person_id = ContactPerson.id'
						)
					)
				);
				$joins = array_merge($joins, $contact_joins);
			}
			
			$this->paginate['BusinessSession'] = array(
				'conditions' => $conditions,
				'contain' => array('BusinessSessionType', 'BusinessSessionState', 'User'),
				'fields' => array('*', 'SUM(Cost.amount) as celkem'),
				'group' => array('BusinessSession.id'),
				'limit' => 30,
				'order' => array('BusinessSession.date' => 'desc'),
				'joins' => $joins
			);
			$business_sessions = $this->paginate('BusinessSession');
			
			foreach ($business_sessions as $index => $business_session) {
				$cost = $this->BusinessSession->Cost->find('first', array(
					'fields' => array('SUM(Cost.amount)'),
					'conditions' => array('Cost.business_session_id' => $business_session['BusinessSession']['id']),
					'contain' => array(),
					'group' => array('Cost.business_session_id')
				));
				$business_sessions[$index][0]['celkem'] = $cost[0]['SUM(`Cost`.`amount`)'];
			}
			$this->set('business_sessions', $business_sessions);
		}
	}

	function user_add() {
		$user_id = $this->user['User']['id'];
		$this->set('user_id', $user_id);
		
		$conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions = array('Purchaser.user_id' => $user_id);
		}
		
		$purchasers = $this->BusinessSession->Purchaser->find('all', array(
			'conditions' => $conditions,
			'order' => array('name' => 'asc'),
			'contain' => array()
		));

		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 8);
		}
		$this->set('redirect', $redirect);
		
		if (isset($this->params['named']['purchaser_id'])) {
			$this->set('purchaser_id', $this->params['named']['purchaser_id']);
			$purchasers = Set::combine($purchasers, '{n}.Purchaser.id', '{n}.Purchaser.name');
			$this->set('purchasers', $purchasers);
		} else {
			$autocomplete_purchasers = array();
			foreach ($purchasers as $purchaser) {
				$autocomplete_purchasers[] = array(
					'label' => $this->BusinessSession->Purchaser->autocomplete_field_info($purchaser['Purchaser']['id']),
					'value' => $purchaser['Purchaser']['id']
				);
			}
			$this->set('purchasers', json_encode($autocomplete_purchasers));
		}
		
		// pridavam jednani ke konkretnimu partnerovi
		if (isset($this->params['named']['purchaser_id'])) {
			$purchaser = $this->BusinessSession->Purchaser->find('first', array(
				'conditions' => array('Purchaser.id' => $this->params['named']['purchaser_id']),
				'contain' => array()
			));
				
			if (!$this->BusinessSession->checkUser($this->user, $purchaser['Purchaser']['user_id'])) {
				$this->Session->setFlash('Neoprávněný přístup. Nemáte právo přidávat jednání k tomuto odběrateli.');
				$this->redirect($redirect);
			}
			$this->set(compact('purchaser'));
			$this->left_menu_list = array('purchasers', 'purchaser_detailed');
			$this->set('active_tab', 'purchasers');
		}
		
		$this->set('monthNames', $this->monthNames);
		
		if (isset($this->data)) {
			$data = $this->data;
			
			if (isset($this->data['BusinessSessionsCost'])) {
				foreach ($this->data['BusinessSessionsCost'] as $index => $cost) {
					if (empty($cost['name']) && empty($cost['price']) && empty($cost['quantity'])) {
						unset($this->data['BusinessSessionsCost'][$index]);
					}
				}
				if (empty($this->data['BusinessSessionsCost'])) {
					unset($this->data['BusinessSessionsCost']);
				}
			}
			
			if (!empty($this->data['BusinessSession']['date'])) {
				$this->data['BusinessSession']['date'] = cal2db_date($this->data['BusinessSession']['date']);
				$this->data['BusinessSession']['date'] = array_merge($this->data['BusinessSession']['date'], $this->data['BusinessSession']['time']);
			}

			$this->data['BusinessSession']['admin_user_id'] = $this->data['BusinessSession']['user_id'] = $this->user['User']['id'];
			$this->data['BusinessSession']['business_session_state_id'] = 1;
			
			if (!$this->data['BusinessSession']['is_education']) {
				unset($this->data['Contract']);
			} else {
				foreach ($this->data['Contract'] as &$contract) {
					// nastaveni dat
					$contract['admin_user_id'] = $contract['user_id'] = $this->user['User']['id'];
					$contract['confirmed'] = false;
					$contract['confirm_requirement'] = false;
					$contract['amount_vat'] = ceil(price_vat($contract['amount'], $contract['vat']));
					// doplnim adresu
					$contact_person_id = $contract['contact_person_id'];
					$contact_person = $this->BusinessSession->Contract->ContactPerson->find('first', array(
						'conditions' => array('ContactPerson.id' => $contact_person_id),
						'contain' => array('Address'),
					));
					$contract['birthday'] = $contact_person['ContactPerson']['birthday'];
					$contract['birth_certificate_number'] = $contact_person['ContactPerson']['birth_certificate_number'];
					$contract['street'] = $contact_person['Address']['street'];
					$contract['number'] = $contact_person['Address']['number'];
					$contract['city'] = $contact_person['Address']['city'];
					$contract['zip'] = $contact_person['Address']['zip'];
				}
			}

			if ($this->BusinessSession->saveAll($this->data)) {
				$this->Session->setFlash('Obchodní jednání bylo uloženo');
				$this->redirect($redirect);
			} else {
				$this->data = $data;
				$this->Session->setFlash('Obchodní jednání se nepodařilo uložit, opakujte prosím akci');
			}
		} else {
			if (isset($this->params['named']['purchaser_id'])) {
				$this->data['BusinessSession']['purchaser_id'] = $this->params['named']['purchaser_id'];
				$this->data['BusinessSession']['purchaser_name'] = $this->BusinessSession->Purchaser->autocomplete_field_info($this->params['named']['purchaser_id']);
			}
		}
		
		
		$cost_types = $this->BusinessSession->BusinessSessionsCost->CostType->find('list');
		$this->set('cost_types', $cost_types);
		
		$contract_types = $this->BusinessSession->Contract->ContractType->find('list', array(
			'fields' => array('ContractType.id', 'ContractType.name')
		));
		$this->set('contract_types', $contract_types);
		$this->set('user', $this->user);
		$this->set('vat', $this->BusinessSession->Contract->vat);
		$this->set('months', months());
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, které chcete upravit');
			$this->redirect($this->index_link);
		}
		
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array(
				'BusinessSessionsCost',
				'Contract'
			)
		));
		
		if (empty($business_session)) {
			$this->Session->setFlash('Zvolené obchodní jednání neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo upravit toto jednání.');
			$this->redirect($this->index_link);
		}
		
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 8);
		}
		$this->set('redirect', $redirect);
		
		$this->set('business_session', $business_session);
		// do leveho menu pridam polozku pro detaily partnera
		$this->left_menu_list[] = 'business_session_detailed';
		
		$user_id = $this->user['User']['id'];
		$this->set('user_id', $user_id);
				
		$this->set('monthNames', $this->monthNames);
		
		if (isset($this->data)) {
			$data = $this->data;

			if (isset($this->data['BusinessSessionsCost'])) {
				foreach ($this->data['BusinessSessionsCost'] as $index => $cost) {
					if (empty($cost['name']) && empty($cost['price']) && empty($cost['quantity'])) {
						unset($this->data['BusinessSessionsCost'][$index]);
					}
				}
				if (empty($this->data['BusinessSessionsCost'])) {
					unset($this->data['BusinessSessionsCost']);
				}
			}
			
			$this->data['BusinessSession']['date'] = cal2db_date($this->data['BusinessSession']['date']);
			$this->data['BusinessSession']['date'] = array_merge($this->data['BusinessSession']['date'], $this->data['BusinessSession']['time']);

			if (empty($this->data['BusinessSession']['purchaser_id'])) {
				unset($this->data['BusinessSession']['purchaser_id']);
			}
			
			// pri zmene obchodniho partnera smazu prizvane kontaktni osoby
			if (
				isset($this->data['BusinessSession']['purchaser_id']) &&
				$this->data['BusinessSession']['purchaser_id'] != $business_session['BusinessSession']['purchaser_id']
			) {
				$this->BusinessSession->BusinessSessionsContactPerson->deleteAll(array(
					'business_session_id' => $this->data['BusinessSession']['id']
				));
			}
			
			if (!$this->data['BusinessSession']['is_education']) {
				unset($this->data['Contract']);
			} else {
				foreach ($this->data['Contract'] as &$contract) {
					$is_editable = (!(isset($contract['confirmed']) && $contract['confirmed']) && !(isset($contract['confirm_requirement']) && $contract['confirm_requirement']));
					if ($is_editable) {
						// nastaveni dat
						$contract['admin_user_id'] = $contract['user_id'] = $this->user['User']['id'];
						$contract['confirmed'] = false;
						$contract['confirm_requirement'] = false;
						$contract['amount_vat'] = ceil(price_vat($contract['amount'], $contract['vat']));
						// doplnim adresu
						$contact_person_id = $contract['contact_person_id'];
						$contact_person = $this->BusinessSession->Contract->ContactPerson->find('first', array(
							'conditions' => array('ContactPerson.id' => $contact_person_id),
							'contain' => array('Address'),
						));
						$contract['birthday'] = $contact_person['ContactPerson']['birthday'];
						$contract['birth_certificate_number'] = $contact_person['ContactPerson']['birth_certificate_number'];
						$contract['street'] = $contact_person['Address']['street'];
						$contract['number'] = $contact_person['Address']['number'];
						$contract['city'] = $contact_person['Address']['city'];
						$contract['zip'] = $contact_person['Address']['zip'];
					}
				}
			}

			$datasource = $this->BusinessSession->getDataSource();
			$datasource->begin($this->BusinessSession);
			// smazu vsechny puvodni naklady
			$costs_to_del = $this->BusinessSession->BusinessSessionsCost->find('all', array(
				'conditions' => array('BusinessSessionsCost.business_session_id' => $id),
				'contain' => array(),
				'fields' => array('BusinessSessionsCost.id')
			));
			$cost_deleted = true;
			foreach ($costs_to_del as $cost_to_del) {
				$cost_deleted = $cost_deleted && $this->BusinessSession->BusinessSessionsCost->delete($cost_to_del['BusinessSessionsCost']['id']);
			}

			if ($cost_deleted) {
				// saveallem ulozim vsechno zaraz
				if ($this->BusinessSession->saveAll($this->data)) {
					$datasource->commit($this->BusinessSession);
					$this->Session->setFlash('Obchodní jednání bylo uloženo.');
					$this->redirect($redirect);
				} else {
					$datasource->rollback($this->BusinessSession);
					$this->Session->setFlash('Obchodní jednání se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
					$this->data = $data;
				}
			} else {
				$datasource->rollback($this->BusinessSession);
				$this->Session->setFlash('Obchodní jednání se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
				$this->data = $data;
			}
		} else {
			$this->data = $business_session;
			$time = explode(' ', $business_session['BusinessSession']['date']);
			$date = $time[0];
			$this->data['BusinessSession']['time'] = $time[1];
			$this->data['BusinessSession']['date'] = db2cal_date($date);
			$this->data['BusinessSession']['purchaser_name'] = $this->BusinessSession->Purchaser->autocomplete_field_info($business_session['BusinessSession']['purchaser_id']);
			if (!empty($business_session['Contract'])) {
				$this->data['BusinessSession']['is_education'] = true;
				foreach ($this->data['Contract'] as &$contract) {
					$contract['contact_person_name'] = $this->BusinessSession->Contract->ContactPerson->autocomplete_field_info($contract['contact_person_id']);
				}
			}
		}
		
		$cost_types = $this->BusinessSession->BusinessSessionsCost->CostType->find('list');
		$this->set('cost_types', $cost_types);
		
		$contract_types = $this->BusinessSession->Contract->ContractType->find('list', array(
			'fields' => array('ContractType.id', 'ContractType.name')
		));
		$this->set('contract_types', $contract_types);
		$this->set('user', $this->user);
		$this->set('vat', $this->BusinessSession->Contract->vat);
		$this->set('months', months());
	}
	
	function user_invite($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, ke kterému chcete přidat kontaktní osoby');
			$this->redirect($this->index_link);
		}
		
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array(
				'BusinessSessionsContactPerson'
			)
		));
		
		if (empty($business_session)) {
			$this->Session->setFlash('Zvolené obchodní jednání neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo pozvat kontaktní osoby na toto jednání.');
			$this->redirect($this->index_link);
		}
		
		$contact_people = $this->BusinessSession->BusinessSessionsContactPerson->ContactPerson->find('all', array(
			'conditions' => array(
				'ContactPerson.business_partner_id' => $business_session['BusinessSession']['business_partner_id'],
				'ContactPerson.active' => true
			),
			'contain' => array('BusinessPartner'),
			'order' => array('last_name' => 'asc')
		));
		
		$this->set('contact_people', $contact_people);
		$this->set('business_session', $business_session);
		// do leveho menu pridam polozku pro detaily partnera
		$this->left_menu_list[] = 'business_session_detailed';
		
		if (isset($this->data)) {
			$this->data = array_filter($this->data['BusinessSessionsContactPerson'], array('BusinessSessionsController', 'filter_not_checked'));
			$this->BusinessSession->BusinessSessionsContactPerson->deleteAll(
				array('business_session_id' => $business_session['BusinessSession']['id'])
			);
			$this->BusinessSession->BusinessSessionsContactPerson->saveAll($this->data);
			$this->Session->setFlash('Přizvané kontaktní osoby byly upraveny');
			$this->redirect(array('controller' => 'business_sessions', 'action' => 'view', $business_session['BusinessSession']['id']));
		} else {
			foreach ($business_session['BusinessSessionsContactPerson'] as $contact_person) {
				$this->data['BusinessSessionsContactPerson'][$contact_person['contact_person_id']] = $contact_person;
			}
		}
	}
	
	function user_close($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 8);
		}
		
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, které chcete uzavřít');
			$this->redirect($redirect);
		}
		
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array()
		));
		
		if (empty($business_session)) {
			$this->Session->setFlash('Zvolené obchodní jednání neexistuje');
			$this->redirect($redirect);
		}
		
		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemůžete uzavřít toto obchodní jednání.');
			$this->redirect($this->index_link);
		}
		
		$business_session['BusinessSession']['business_session_state_id'] = 2;
		if ($this->BusinessSession->save($business_session)) {
			$this->Session->setFlash('Obchodní jednání bylo uzavřeno');
		} else {
			$this->Session->setFlash('Obchodní jednání se nepodařilo uzavřít, opakujte prosím akci');
		}
		$this->redirect($redirect);
	}
	
	function user_storno($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 8);
		}
		
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, které chcete stornovat');
			$this->redirect($redirect);
		}
		
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array()
		));
		
		if (empty($business_session)) {
			$this->Session->setFlash('Zvolené obchodní jednání neexistuje');
			$this->redirect($redirect);
		}
		
		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemůžete stornovat toto obchodní jednání.');
			$this->redirect($redirect);
		}
		
		$business_session['BusinessSession']['business_session_state_id'] = 3;
		if ($this->BusinessSession->save($business_session)) {
			$this->Session->setFlash('Obchodní jednání bylo stornováno');
		} else {
			$this->Session->setFlash('Obchodní jednání se nepodařilo stornovat, opakujte prosím akci');
		}
		$this->redirect($redirect);
	}
	
	function user_delete($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'business_sessions', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 8);
		}
		
		if (!$id) {
			$this->Session->setFlash('Není určeno obchodní jednání, které chcete smazat.');
			$this->redirect($this->index_link);
		}
		
		$business_session = $this->BusinessSession->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array()
		));

		if (!$this->BusinessSession->checkUser($this->user, $business_session['BusinessSession']['admin_user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemůžete smazat toto obchodní jednání.');
			$this->redirect($this->index_link);
		}

		if ($this->BusinessSession->delete($id)) {
			$this->Session->setFlash('Obchodní jednání bylo odstraněno.');
		} else {
			$this->Session->setFlash('Obchodní jednání se nepodařilo odstranit.');
		}
		$this->redirect($redirect);
	}
	
	function filter_not_checked($a) {
		return ($a['contact_person_id'] != 0);
	}
}
