<?php
class ContactPeopleController extends AppController {
	var $name = 'ContactPeople';
	
	var $index_link = array('controller' => 'contact_people', 'action' => 'index');
	
	var $left_menu_list = array('contact_people');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('active_tab', 'contact_people');
	}
	
	function beforeRender(){
		parent::beforeRender();
		$this->set('left_menu_list', $this->left_menu_list);
	}
	
	function user_index() {
		$user_id = $this->user['User']['id'];
		
		$business_partner_conditions = array('Purchaser.id = ContactPerson.purchaser_id');
		if ($this->user['User']['user_type_id'] == 3) {
			$business_partner_conditions['Purchaser.user_id'] = $user_id;
		}
		
		$conditions = array('ContactPerson.active' => true, 'Purchaser.active' => true, 'BusinessPartner.active' => true);
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'contact_people') {
			$this->Session->delete('Search.ContactPersonSearch');
			$this->redirect(array('controller' => 'contact_people', 'action' => 'index'));
		}
		
		// pokud chci vysledky vyhledavani
		if ( isset($this->data['ContactPersonSearch']['ContactPerson']['search_form']) && $this->data['ContactPersonSearch']['ContactPerson']['search_form'] == 1 ){
			$this->Session->write('Search.ContactPersonSearch', $this->data['ContactPersonSearch']);
			$conditions = $this->ContactPerson->do_form_search($conditions, $this->data['ContactPersonSearch']);
		} elseif ($this->Session->check('Search.ContactPersonSearch')) {
			$this->data['ContactPersonSearch'] = $this->Session->read('Search.ContactPersonSearch');
			$conditions = $this->ContactPerson->do_form_search($conditions, $this->data['ContactPersonSearch']);
		}

		$this->ContactPerson->virtualFields['purchaser_name'] = $this->ContactPerson->Purchaser->virtualFields['name'];
		$this->paginate['ContactPerson'] = array(
			'conditions' => $conditions,
			'limit' => 30,
			'fields' => array('Purchaser.*', 'ContactPerson.*'),
			'contain' => array(
				'Anniversary' => array(
					'fields' => array('id')
				)
			),
			'joins' => array(
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'INNER',
					'conditions' => $business_partner_conditions
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'INNER',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				)
			),
		);
		$contact_people = $this->paginate('ContactPerson');
		unset($this->ContactPerson->virtualFields['purchaser_name']);
		$this->set('contact_people', $contact_people);
		
		$find = $this->paginate['ContactPerson'];
		unset($find['limit']);
		unset($find['fields']);
		$this->set('find', $find);
		
		$this->set('export_fields', $this->ContactPerson->export_fields);
		
		$back_link = array('controller' => 'contact_people', 'action' => 'index') + $this->passedArgs;
		$this->set('back_link', base64_encode(serialize($back_link)));
	}
	
	function user_view($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena kontaktní osoba, kterou chcete zobrazit');
			$this->redirect($this->index_link);
		}
		
		$contact_person = $this->ContactPerson->find('first', array(
			'conditions' => array('ContactPerson.id' => $id),
			'contain' => array('Purchaser')
		));
		
		if (empty($contact_person)) {
			$this->Session->setFlash('Zvolená kontaktní osoba neexistuje');
			$this->redirect($this->index_link);
		}
		
		if (!$this->ContactPerson->checkUser($this->user, $contact_person['Purchaser']['user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo pro zobrazení této kontaktní osoby.');
			$this->redirect($this->index_link);
		}
		
		$this->set('contact_person', $contact_person);
		
		$anniversaries = $this->ContactPerson->Anniversary->find('all', array(
			'conditions' => array('Anniversary.contact_person_id' => $id),
			'contain' => array(
				'AnniversaryType',
				'AnniversaryAction'
			)
		));
		$this->set('anniversaries', $anniversaries);
		
		$this->left_menu_list[] = 'contact_person_detailed';
	}
	
	function user_add() {
		$user_id = $this->user['User']['id'];
		
		$purchasers_conditions = array('Purchaser.active' => true);
		if ($this->user['User']['user_type_id'] == 3) {
			$purchasers_conditions = array('Purchaser.user_id' => $user_id);
		}

		$purchasers = $this->ContactPerson->Purchaser->find('all', array(
			'conditions' => $purchasers_conditions,
			'order' => array('name' => 'asc'),
			'contain' => array()
		));
		
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contact_people', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 7);
		}
		$this->set('redirect', $redirect);
		
		if (empty($purchasers)) {
			$this->Session->setFlash('Nemáte vloženy žádné odběratele, ke kterým chcete přidat kontaktní osobu. Vložte prosím nejprve odběratele');
			$this->redirect($redirect);
		}
		
		if (isset($this->params['named']['purchaser_id'])) {
			$purchaser_id = $this->params['named']['purchaser_id'];
			$this->set('purchaser_id', $purchaser_id);
			
			$thePurchaser = $this->ContactPerson->Purchaser->find('first', array(
				'conditions' => array('Purchaser.id' => $purchaser_id),
				'contain' => array()
			));
			$this->set('purchaser', $thePurchaser);
			
			$purchasers = Set::combine($purchasers, '{n}.Purchaser.id', '{n}.Purchaser.name');
			$this->set('purchasers', $purchasers);
			$this->left_menu_list = array('purchasers', 'purchaser_detailed');
			$this->set('active_tab', 'purchasers');
		} else {
			$autocomplete_purchasers = array();
			foreach ($purchasers as $purchaser) {
				$autocomplete_purchasers[] = array(
					'label' => $this->ContactPerson->Purchaser->autocomplete_field_info($purchaser['Purchaser']['id']),
					'value' => $purchaser['Purchaser']['id']
				);
			}
			$this->set('purchasers', json_encode($autocomplete_purchasers));
		}
		
		if (isset($this->data)) {
			if (empty($this->data['ContactPerson']['purchaser_id']) && !empty($this->data['ContactPerson']['purchaser_id_old'])) {
				$this->data['ContactPerson']['purchaser_id'] = $this->data['ContactPerson']['purchaser_id_old'];
			}

			// vytvorim vyroci typu svatek s akci upozornit, pokud najdu krestni jmeno v tabulce jmen
			$query = '
			SELECT *
			FROM name_days
			WHERE name_days.name = "' . $this->data['ContactPerson']['first_name'] . '"';

			$name_day = $this->ContactPerson->query($query);
			
			// potrebuju z cisla dne v roce zjistit datum
			if (!empty($name_day)) {
				// svatky mam v tabulce pro prestupny rok, proto kdyz hledam datum, budu pocitat v roce 1972, ktery byl prvni prestupny
				$start_date = 2 * 365;
				$date = date('Y') . '-' . date('m-d', ($start_date + $name_day[0]['name_days']['day_in_year'] - 1) * 24 * 60 * 60);
				$this->data['Anniversary'][0] = array(
					'date' => $date,
					'anniversary_type_id' => 1,
					'anniversary_action_id' => 2
				);
			}

			if ($this->ContactPerson->saveAll($this->data)) {
				$this->Session->setFlash('Kontaktní osoba byla uložena');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Kontaktní osobu se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			if (isset($thePurchaser)) {
				$this->data['ContactPerson']['purchaser_name'] = $this->ContactPerson->Purchaser->autocomplete_field_info($thePurchaser['Purchaser']['id']);
			}
		}
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena kontaktní osoba, kterou chcete upravovat');
			$this->redirect($this->index_link);
		}
		
		$purchasers_conditions = array();
		if ($this->user['User']['user_type_id'] == 3) {
			$purchasers_conditions = array('Purchaser.user_id' => $this->user['User']['id']);
		}
		
		$contact_person = $this->ContactPerson->find('first', array(
			'conditions' => array('ContactPerson.id' => $id, 'ContactPerson.active' => 'true'),
			'contain' => array('Purchaser', 'Address')
		));
		
		// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contact_people', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 7);
		}
		$this->set('redirect', $redirect);
		
		if (empty($contact_person)) {
			$this->Session->setFlash('Zvolená kontaktní osoba neexistuje');
			$this->redirect($redirect);
		}
		
		if (!$this->ContactPerson->checkUser($this->user, $contact_person['Purchaser']['user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo upravovat tuto kontaktní osobu.');
			$this->redirect($redirect);
		}
		$this->set('contact_person', $contact_person);
		$this->left_menu_list[] = 'contact_person_detailed';
		
		if (isset($this->params['named']['purchaser_id'])) {
			$this->set('purchaser_id', $this->params['named']['purchaser_id']);
			$purchaser = $this->ContactPerson->Purchaser->find('first', array(
				'conditions' => array('Purchaser.id' => $this->params['named']['purchaser_id']),
				'contain' => array()
			));
			
			$this->set(compact('purchaser'));
			$this->left_menu_list = array('purchasers', 'purchaser_detailed');
			$this->set('active_tab', 'purchasers');
		}
		
		$user_id = $this->user['User']['id'];
		
		$purchasers = $this->ContactPerson->Purchaser->find('all', array(
			'conditions' => $purchasers_conditions,
			'order' => array('name' => 'asc'),
			'contain' => array()
		));

		$autocomplete_purchasers = array();
		foreach ($purchasers as $purchaser) {
			$autocomplete_purchasers[] = array(
				'label' => $this->ContactPerson->Purchaser->autocomplete_field_info($purchaser['Purchaser']['id']),
				'value' => $purchaser['Purchaser']['id']
			);
		}
		$this->set('purchasers', json_encode($autocomplete_purchasers));
		
		if (isset($this->data)) {
			$data = $this->data;
			if (empty($this->data['ContactPerson']['purchaser_id']) && !empty($this->data['ContactPerson']['purchaser_id_old'])) {
				$this->data['ContactPerson']['purchaser_id'] = $this->data['ContactPerson']['purchaser_id_old'];
			}
			
			// podivam se, jestli se zmenilo krestni jmeno
			if ($this->data['ContactPerson']['first_name'] != $contact_person['ContactPerson']['first_name']) {
				// zmenilo se, musim upravit svatek, pokud nejakej kontaktni osoba mela
				$db_name_day = $this->ContactPerson->Anniversary->find('first', array(
					'conditions' => array(
						'Anniversary.contact_person_id' => $id,
						'Anniversary.anniversary_type_id' => 1
					),
					'contain' => array()
				));	
				
				// a vytvorim
				$query = '
				SELECT *
				FROM name_days
				WHERE name_days.name = "' . $this->data['ContactPerson']['first_name'] . '"';
	
				$name_day = $this->ContactPerson->query($query);
				
				// potrebuju z cisla dne v roce zjistit datum
				if (!empty($name_day)) {
					// svatky mam v tabulce pro prestupny rok, proto kdyz hledam datum, budu pocitat v roce 1972, ktery byl prvni prestupny
					$start_date = 2 * 365;
					$date = date('Y') . '-' . date('m-d', ($start_date + $name_day[0]['name_days']['day_in_year'] - 1) * 24 * 60 * 60);
					$this->data['Anniversary'][0] = array(
						'date' => $date,
						'anniversary_type_id' => 1,
						'anniversary_action_id' => 2
					);
					if (!empty($db_name_day)) {
						$this->data['Anniversary'][0]['id'] = $db_name_day['Anniversary']['id'];
					}
				} else {
					// pokud mela osoba svatek a ted uz nema, tak musim z db smazat
					$this->ContactPerson->Anniversary->delete($db_name_day['Anniversary']['id']);
				}
			}

			if ($this->ContactPerson->saveAll($this->data)) {
				$this->Session->setFlash('Kontaktní osoba byla upravena');
				$this->redirect($redirect);
			} else {
				$this->data = $data;
				$this->Session->setFlash('Kontaktní osobu se nepodařilo upravit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $contact_person;
			$this->data['ContactPerson']['purchaser_name'] = $this->ContactPerson->Purchaser->autocomplete_field_info($contact_person['ContactPerson']['purchaser_id']);
		}
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není určena kontaktní osoba, kterou chcete upravovat');
			$this->redirect($this->index_link);
		}
		
		$contact_person = $this->ContactPerson->find('first', array(
			'conditions' => array('ContactPerson.id' => $id),
			'contain' => array('Purchaser')
		));
		
		if (empty($contact_person)) {
			$this->Session->setFlash('Zvolená kontaktní osoba neexistuje');
			$this->redirect($this->index_link);
		}
		
			// nastavim akci pro presmerovani
		$redirect = array('controller' => 'contact_people', 'action' => 'index') + $this->passedArgs;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if (is_serialized($redirect)) {
				$redirect = unserialize($redirect);
			}
		} elseif (isset($this->params['named']['purchaser_id'])) {
			$redirect = array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => 7);
		}
		
		if (!$this->ContactPerson->checkUser($this->user, $contact_person['Purchaser']['user_id'])) {
			$this->Session->setFlash('Neoprávněný přístup. Nemáte právo smazat tuto kontaktni osobu.');
			$this->redirect($this->index_link);
		}
		
		if ($this->ContactPerson->delete($id)) {
			$this->Session->setFlash('Kontaktní osoba byla odstraněna');
		} else {
			$this->Session->setFlash('Kontatní osobu se nepodařilo odstranit, opakujte prosím akci');
		}
		$this->redirect($redirect);
	}
	
	function user_autocomplete_list($business_partner_id = null, $purchaser_id = null) {
		$term = null;
		if ($_GET['term']) {
			$term = $_GET['term'];
		}
	
		echo $this->ContactPerson->autocomplete_list($this->user, $term, $business_partner_id, $purchaser_id);
		die();
	}
}
?>
