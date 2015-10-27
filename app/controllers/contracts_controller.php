<?php 
class ContractsController extends AppController {
	var $name = 'Contracts';
	
	var $left_menu_list = array('contracts');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'contracts');
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		// model, ze ktereho metodu volam
		$model = 'Contract';
		$this->set('model', $model);
		
		if (isset($this->params['named']['reset'])) {
			$this->Session->delete('Search.' . $model . 'Form');
			$this->redirect(array('controller' => 'contracts', 'action' => 'index'));
		}
		
		$conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}

		// pokud chci vysledky vyhledavani
		if (isset($this->data[$model . 'Form'][$model]['search_form']) && $this->data[$model . 'Form'][$model]['search_form'] == 1) {
			$this->Session->write('Search.' . $model . 'Form', $this->data[$model . 'Form']);
			$conditions = $this->$model->do_form_search($conditions, $this->data[$model . 'Form']);
		} elseif ($this->Session->check('Search.' . $model . 'Form')) {
			$this->data[$model . 'Form'] = $this->Session->read('Search.' . $model . 'Form');
			$conditions = $this->$model->do_form_search($conditions, $this->data[$model . 'Form']);
		}

		$this->$model->virtualFields['contact_person_name'] = $this->$model->ContactPerson->virtualFields['name'];
		$this->paginate = array(
			'conditions' => $conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'contact_people',
					'alias' => 'ContactPerson',
					'type' => 'INNER',
					'conditions' => array('ContactPerson.id = ' . $model . '.contact_person_id')
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
					'conditions' => array($model . '.user_id = User.id')
				)
			),
			'fields' => array(
				$model . '.*',

				'User.id',
				'User.last_name',
			),
			'order' => array(
				$model . '.created' => 'desc',
			)
		);
		$contracts = $this->paginate();
		unset($this->$model->virtualFields['contact_person_name']);
		$this->set('contracts', $contracts);
		
		$this->set('find', $this->paginate);
		
		$export_fields = $this->$model->export_fields;
		$this->set('export_fields', $export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$users_conditions = array('User.id' => $this->user['User']['id']);
		}
		$users = $this->$model->User->find('all', array(
			'conditions' => $users_conditions,
			'contain' => array(),
			'fields' => array('User.id', 'User.first_name', 'User.last_name')
		));
		$users = Set::combine($users, '{n}.User.id', array('{0} {1}', '{n}.User.first_name', '{n}.User.last_name'));
		$this->set('users', $users);
		
		$this->set('user', $this->user);
	}
	
	function user_pdf($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou dohodu chcete zobrazit');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}
		
		$this->Contract->virtualFields['one_line'] = $this->Contract->one_line;
		$contract = $this->Contract->find('first', array(
			'conditions' => $conditions,
			'contain' => array(
				'ContactPerson',
				'ContractType'
			)
		));
		unset($this->Contract->virtualFields['one_line']);

		if (empty($contract)) {
			$this->Session->setFlash('Dohoda, kterou chcete upravit, neexistuje');
			$this->redirect($redirect);
		}

		// vygeneruju datum splatnosti dohody (desaty den nasledujiciho mesice)
		$contract['Contract']['due_date'] = $this->Contract->due_date($contract['Contract']['id']);
		
		// vygeneruju datum podpisu dohody
		$contract['Contract']['signature_date'] = $this->Contract->signature_date($contract['Contract']['id']);
		
		$this->set('contract', $contract);
		$this->layout = 'pdf';
	}
	
	function user_add() {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $this->params['named']['business_partner_id']);
		}
		$this->set('redirect', $redirect);

		if (isset($this->data)) {
			// nastaveni dat
			$this->data['Contract']['user_id'] = $this->user['User']['id'];
			$this->data['Contract']['confirmed'] = false;
			$this->data['Contract']['confirm_requirement'] = false;
			$this->data['Contract']['amount'] = floor(price_wout_vat($this->data['Contract']['amount_vat'], $this->data['Contract']['vat']));
			// doplnim adresu
			$contact_person_id = $this->data['Contract']['contact_person_id'];
			$contact_person = $this->Contract->ContactPerson->find('first', array(
				'conditions' => array('ContactPerson.id' => $contact_person_id),
				'contain' => array('Address'),
			));
			$this->data['Contract']['birthday'] = $contact_person['ContactPerson']['birthday'];
			$this->data['Contract']['birth_certificate_number'] = $contact_person['ContactPerson']['birth_certificate_number'];
			$this->data['Contract']['street'] = $contact_person['Address']['street'];
			$this->data['Contract']['number'] = $contact_person['Address']['number'];
			$this->data['Contract']['city'] = $contact_person['Address']['city'];
			$this->data['Contract']['zip'] = $contact_person['Address']['zip'];

			if ($this->Contract->saveAll($this->data)) {
				$this->Session->setFlash('Dohoda byla uložena');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Dohodu se nepodařilo uložit, opravte chyby ve formuláři a uložte ji prosím znovu.');
			}
		} else {
			$this->data['Contract']['year'] = 2015;
			if (isset($this->params['named']['contact_person_id'])) {
				$this->data['Contract']['contact_person_id'] = $this->params['named']['contact_person_id'];
				$this->data['Contract']['contact_person_name'] = $this->Contract->ContactPerson->autocomplete_field_info($this->params['named']['contact_person_id']);
			}
		}
		
		$contract_types = $this->Contract->ContractType->find('list', array(
			'fields' => array('ContractType.id', 'ContractType.name')
		));
		$this->set('contract_types', $contract_types);
		$this->set('user', $this->user);
		$this->set('vat', $this->Contract->vat);
		$this->set('months', months());
	}
	
	function user_edit($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou dohodu chcete upravit');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}
		
		$contract = $this->Contract->find('first', array(
			'conditions' => $conditions,
			'contain' => array()
		));
		
		if (empty($contract)) {
			$this->Session->setFlash('Dohoda, kterou chcete upravit, neexistuje');
			$this->redirect($redirect);
		}

		if ($contract['Contract']['confirmed']) {
			$this->Session->setFlash('Dohoda, kterou chcete upravit, již byla schválena a nelze ji proto upravit');
			$this->redirect($redirect);
		}
		
		if ($contract['Contract']['confirm_requirement']) {
			$this->Session->setFlash('Dohoda, kterou chcete upravit, byla odeslána ke schválení a nelze ji proto upravit');
			$this->redirect($redirect);
		}
		
		if (isset($this->data)) {
			// pokud jsem zmenil kontaktni osobu
			if ($this->data['Contract']['contact_person_id'] != $contract['Contract']['contact_person_id']) {
				// musim zmenit adresu na dohode
				$contact_person_id = $this->data['Contract']['contact_person_id'];
				$contact_person = $this->ContactPerson->find('first', array(
					'conditions' => array('ContactPerson.id' => $contact_person_id),
					'contain' => array('Address'),
				));
				$this->data['Contract']['street'] = $contact_person['Address']['street'];
				$this->data['Contract']['number'] = $contact_person['Address']['number'];
				$this->data['Contract']['city'] = $contact_person['Address']['city'];
				$this->data['Contract']['zip'] = $contact_person['Address']['zip'];
			}
			
			$this->data['Contract']['amount'] = floor(price_wout_vat($this->data['Contract']['amount_vat'], $this->data['Contract']['vat']));
			
			if ($this->Contract->saveAll($this->data)) {
				$this->Session->setFlash('Dohoda byla upravena');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Dohodu se nepodařilo upravit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $contract;
			$this->data['Contract']['vat_vis'] = $this->data['Contract']['vat'];
			$this->data['Contract']['contact_person_name'] = $this->Contract->ContactPerson->autocomplete_field_info($this->data['Contract']['contact_person_id']);
		}
		
		$contract_types = $this->Contract->ContractType->find('list', array(
			'fields' => array('ContractType.id', 'ContractType.name')
		));
		$this->set('contract_types', $contract_types);
		$this->set('user', $this->user);
		$this->set('months', months());
	}
	
	function user_confirm_require($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou dohodu chcete nechat schválit');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}
		
		if (!$this->Contract->hasAny($conditions)) {
			$this->Session->setFlash('Dohoda, kterou chcete nechat schválit, neexistuje');
			$this->redirect($redirect);
		}
		
		$save = array(
			'Contract' => array(
				'id' => $id,
				'confirm_requirement' => true
			)
		);
		if ($this->Contract->save($save)) {
			$this->Session->setFlash('Požadavek na schválení dohody byl odeslán');
		} else {
			$this->Session->setFlash('Požadavek na schválení dohody se nepodařilo odeslat');
		}
		$this->redirect($redirect);
	}
	
	function user_cancel_confirm_requirement($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, pro kterou dohodu chcete zrušit požadavek');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}
		
		if (!$this->Contract->hasAny($conditions)) {
			$this->Session->setFlash('Dohoda, pro kterou chcete zrušit požadavek, neexistuje');
			$this->redirect($redirect);
		}
		
		$save = array(
			'Contract' => array(
				'id' => $id,
				'confirm_requirement' => false
			)
		);
		if ($this->Contract->save($save)) {
			$this->Session->setFlash('Požadavek na schválení dohody byl zrušen');
		} else {
			$this->Session->setFlash('Požadavek na schválení dohody se nepodařilo zrušen');
		}
		$this->redirect($redirect);
	}
	
	function user_confirm($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou dohodu chcete schválit');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}

		$contract = $this->Contract->find('first', array(
			'conditions' => $conditions,
			'contain' => array()
		));
		
		if (empty($contract)) {
			$this->Session->setFlash('Dohoda, kterou chcete schválit, neexistuje');
			$this->redirect($redirect);
		}
		
		$contract['Contract']['confirmed'] = true;
		
		$datasource = $this->Contract->getDataSource($this->Contract);
		$datasource->begin($this->Contract);
		if ($this->Contract->save($contract)) {
			// odectu penize z uctu obchodniho partnera
			$business_partner = $this->Contract->ContactPerson->get_business_partner($contract['Contract']['contact_person_id']);
			if (!$this->Contract->ContactPerson->Purchaser->BusinessPartner->wallet_transaction($business_partner['BusinessPartner']['id'], -$contract['Contract']['amount'])) {
				$datasource->rollback($this->Contract);
				$this->Session->setFlash('Dohoda nebyla schválena, nepodařilo se odečíst částku z peněženky obchodního partnera');
			} else {
				$datasource->commit($this->Contract);
				$this->Session->setFlash('Dohoda byla schválena');
			}
		} else {
			$this->Session->setFlash('Dohoda nebyla schválena, nepodařilo se uložit dohodu');
		}
		$this->redirect($redirect);
	}
	
	function user_delete($id = null) {
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contracts', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['contact_person_id'])) {
			$redirect = array('controller' => 'contact_people', 'action' => 'view', $this->params['named']['contact_person_id'], 'tab' => 'XXX');
		} elseif (isset($this->params['named']['business_partner_id'])) {
			$redirect = array('controller' => 'business_partners', 'action' => 'index') + $this->passedArgs;
			$this->set('business_partner_id', $business_partner_id);
		}
		$this->set('redirect', $redirect);
		
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou dohodu chcete smazat');
			$this->redirect($redirect);
		}
		
		$conditions = array('Contract.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Contract.user_id'] = $this->user['User']['id'];
		}
		
		$contract = $this->Contract->find('first', array(
			'conditions' => $conditions,
			'contain' => array()
		));
		
		if (empty($contract)) {
			$this->Session->setFlash('Dohoda, kterou chcete smazat, neexistuje');
			$this->redirect($redirect);
		}
		
		if ($contract['Contract']['confirmed']) {
			$this->Session->setFlash('Dohoda, kterou chcete smazat, již byla schválena a nelze ji proto upravit');
			$this->redirect($redirect);
		}
		
		if ($contract['Contract']['confirm_requirement']) {
			$this->Session->setFlash('Dohoda, kterou chcete smazat, byla odeslána ke schválení a nelze ji proto upravit');
			$this->redirect($redirect);
		}
	
		if ($this->Contract->delete($id)) {
			$this->Session->setFlash('Dohoda byla odstraněna');
		} else {
			$this->Session->setFlash('Dohodu se nepodařilo odstranit');
		}
		$this->redirect($redirect);
	}
}
?>