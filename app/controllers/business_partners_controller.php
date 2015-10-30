<?php
class BusinessPartnersController extends AppController {
	var $name = 'BusinessPartners';
	
	var $index_link = array('controller' => 'business_partners', 'action' => 'index');
	
	var $paginate = array(
		'limit' => 30,
		'order' => array('BusinessPartner.name' => 'asc'),
	);
	
	// zakladni nastaveni pro leve menu
	// v konkretni action se da pridat,
	// nebo upravit
	var $left_menu_list = array('business_partners');
	
	function beforeFilter(){
		parent::beforeFilter();
		$this->set('active_tab', 'business_partners');
		
		$this->Auth->allow('address', 'contact_people');
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
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'business_partners') {
			// smazu informace ze session
			$this->Session->delete('Search.BusinessPartnerForm');
			$this->redirect(array('controller' => 'business_partners', 'action' => 'index'));
		}
		
		$conditions = array();
		// rep at vidi jen svoje obchodni partnery nebo obchodni partnery svych odberatelu
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions = array(
				'OR' => array(
					'BusinessPartner.user_id' => $this->user['User']['id'],
					'Purchaser.user_id' => $this->user['User']['id']
				)
			);
		}
		
		// pokud jsou zadany parametry pro vyhledavani ve formulari
		if (isset($this->data['BusinessPartner']['search_form']) && $this->data['BusinessPartner']['search_form'] == 1) {
			$this->Session->write('Search.BusinessPartnerForm', $this->data);
			$conditions = $this->BusinessPartner->do_form_search($conditions, $this->data);
		// jeste zkusim, jestli nejsou zadany v session
		} elseif ($this->Session->check('Search.BusinessPartnerForm')) {
			$this->data = $this->Session->read('Search.BusinessPartnerForm');
			$conditions = $this->BusinessPartner->do_form_search($conditions, $this->data);
		}

		$this->BusinessPartner->virtualFields['address_street_info'] = $this->BusinessPartner->Address->street_info;
		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array(),
			'limit' => 30,
			'fields' => array('DISTINCT BusinessPartner.id', 'BusinessPartner.*', 'Address.*'),
			'joins' => array(
				array(
					'table' => 'addresses',
					'type' => 'INNER',
					'alias' => 'Address',
					'conditions' => array(
						'BusinessPartner.id = Address.business_partner_id',
						'Address.address_type_id = 1'
					)
				),
				array(
					'table' => 'purchasers',
					'alias' => 'Purchaser',
					'type' => 'LEFT',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				)
			)
		);
		$business_partners = $this->paginate('BusinessPartner');

		unset($this->BusinessPartner->virtualFields['address_street_info']);
		$this->set('business_partners', $business_partners);
		
		$find = $this->paginate;
		unset($find['limit']);
		unset($find['fields']);
		$this->set('find', $find);
		
		$this->set('export_fields', $this->BusinessPartner->export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->BusinessPartner->Purchaser->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
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
		$this->left_menu_list[] = 'business_partner_detailed';
		
		if (!$id) {
			$this->Session->setFlash('Není určen obchodní partner, kterého chcete zobrazit');
			$this->redirect($this->index_link);
		}
		
		$business_partner = $this->BusinessPartner->find('first', array(
			'conditions' => array('BusinessPartner.id' => $id),
			'contain' => array()
		));
		
		if (empty($business_partner)) {
			$this->Session->setFlash('Zvolený obchodní partner neexistuje');
			$this->redirect($this->index_link);
		}
		
		$this->set('business_partner', $business_partner);
		
		list($seat_address, $delivery_address, $invoice_address) = $this->BusinessPartner->Address->get_addresses($id);
		$this->set('seat_address', $seat_address);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->BusinessPartner->Purchaser->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
		
		// ODBERATELE TOHOTO OBCHODNIHO PARTNERA
		$purchasers_conditions = array(
			'Purchaser.business_partner_id' => $id,
			'Purchaser.active' => true
		);
		if ($this->user['User']['user_type_id'] == 3) {
			$purchasers_conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}
		
		if (isset($this->params['named']['reset']) && $this->params['named']['reset'] == 'purchasers') {
			$this->Session->delete('Search.PurchaserSearch');
			$this->redirect(array('controller' => 'business_partners', 'action' => 'view', $id, 'tab' => 3));
		}

		// pokud chci vysledky vyhledavani
		if (isset($this->data['PurchaserSearch']['Purchaser']['search_form']) && $this->data['PurchaserSearch']['Purchaser']['search_form'] == 1) {
			$this->Session->write('Search.PurchaserSearch', $this->data['PurchaserSearch']);
			$purchasers_conditions = $this->BusinessPartner->Purchaser->do_form_search($purchasers_conditions, $this->data['PurchaserSearch']);
		} elseif ($this->Session->check('Search.PurchaserSearch')) {
			$this->data['PurchaserSearch'] = $this->Session->read('Search.PurchaserSearch');
			$purchasers_conditions = $this->BusinessPartner->Purchaser->do_form_search($purchasers_conditions, $this->data['PurchaserSearch']);
		}

		unset($this->passedArgs['sort']);
		unset($this->passedArgs['direction']);
		if (isset($this->params['named']['tab']) && $this->params['named']['tab'] == 3) {
			$this->passedArgs['sort'] = $sort_field;
			$this->passedArgs['direction'] = $sort_direction;
		}

		$this->paginate['Purchaser'] = array(
			'conditions' => $purchasers_conditions,
			'contain' => array(),
			'limit' => 30,
			'fields' => array('*'),
			'joins' => array(
				array(
					'table' => 'addresses',
					'type' => 'LEFT',
					'alias' => 'Address',
					'conditions' => array(
						'Purchaser.id = Address.purchaser_id'
					)
				),
				array(
					'table' => 'business_partners',
					'alias' => 'BusinessPartner',
					'type' => 'LEFT',
					'conditions' => array('BusinessPartner.id = Purchaser.business_partner_id')
				)
			)
		);
		$purchasers = $this->paginate('Purchaser');
		$this->set('purchasers', $purchasers);
		
		$this->set('purchasers_paging', $this->params['paging']);
		$purchasers_find = $this->paginate['Purchaser'];
		unset($purchasers_find['limit']);
		unset($purchasers_find['fields']);
		$this->set('purchasers_find', $purchasers_find);
		
		$this->set('purchasers_export_fields', $this->BusinessPartner->Purchaser->export_fields);
		
		// POZNAMKY
		$business_partner_notes = $this->BusinessPartner->BusinessPartnerNote->find('all', array(
			'conditions' => array('BusinessPartnerNote.business_partner_id' => $business_partner['BusinessPartner']['id']),
			'contain' => array(),
			'order' => array('BusinessPartnerNote.created' => 'desc')
		));
		$this->set('business_partner_notes', $business_partner_notes);
	}
	
	function user_search() {
		$this->set('user_id', $this->user['User']['id']);
		
		if (!empty($this->params['named'])) {
			$named = $this->params['named'];
			unset($named['page']);
			unset($named['sort']);
			unset($named['direction']);
			foreach ($named as $key => $item) {
				$indexes = explode('.', $key);
				$this->data[$indexes[0]][$indexes[1]] = $item;
			}
		}
		
		if (isset($this->data)) {
			$conditions = array();
			if (isset($this->data['BusinessPartner'])) {
				foreach ($this->data['BusinessPartner'] as $key => $item) {
					if ($key == 'active') {
						$conditions['BusinessPartner.active'] = $item;
					} elseif (!empty($item)) {
						$conditions[] = 'BusinessPartner.' . $key . ' LIKE \'%%' . $item . '%%\'';
					}
				}
			}
			if (isset($this->data['Address'])) {
				foreach ($this->data['Address'] as $key => $item) {
					if (!empty($item)) {
						$conditions[] = 'Address.' . $key . ' LIKE \'%%' . $item . '%%\'';
					}
				}
			}
			$this->paginate['BusinessPartner'] = array(
				'conditions' => $conditions,
				'limit' => 30,
				'contain' => array('User'),
				'fields' => array('BusinessPartner.*', 'Address.*', 'User.*', 'CONCAT(User.last_name, " ", User.first_name) as full_name'),
				'joins' => array(
					array(
						'table' => 'addresses',
						'type' => 'INNER',
						'alias' => 'Address',
						'conditions' => array(
							'BusinessPartner.id = Address.business_partner_id',
							'Address.address_type_id = 1'
						)
					)
				)
			);
			
			$business_partners = $this->paginate('BusinessPartner');
			$this->set('business_partners', $business_partners);
			$this->set('bonity', $this->bonity);
			
			$find = $this->paginate['BusinessPartner'];
			unset($find['limit']);
			$find['fields'] = $this->BusinessPartner->export_fields;
			$this->set('find', $find);
		}
	}
	
	function user_add() {
		$user_id = $this->user['User']['id'];
		$this->set('user_id', $user_id);
		
		if (isset($this->data)) {
			if (!isset($this->data['BusinessPartner']['ares_search'])) {
				// dogeneruju si nazev obchodniho partnera, pokud nemam zadany
				if (empty($this->data['BusinessPartner']['name']) && (!empty($this->data['BusinessPartner']['first_name']) || !empty($this->data['BusinessPartner']['last_name']))) {
					$name = array(
						$this->data['BusinessPartner']['degree_before'],
						$this->data['BusinessPartner']['first_name'],
						$this->data['BusinessPartner']['last_name'],
						$this->data['BusinessPartner']['degree_after']
					);
					$name = array_filter($name);
					$name = implode(' ', $name);
					$this->data['BusinessPartner']['name'] = $name;
				}

				$this->data['BusinessPartner']['active'] = true;
				$this->data['BusinessPartner']['user_id'] = $this->user['User']['id'];
				
				// pokud chci vytvorit i odberatele
				if ($this->data['Purchaser']['same']) {
					// a obchodniho partnera s danym icem uz mam v systemu
					$business_partner = $this->BusinessPartner->find('first', array(
						'conditions' => array(
							'BusinessPartner.ico' => $this->data['BusinessPartner']['ico'],
						),
						'contain' => array(),
						'fields' => array('BusinessPartner.id', 'BusinessPartner.active')
					));
				}

				$datasource = $this->BusinessPartner->getDataSource();
				$datasource->begin($this->BusinessPartner);
				
				if ($this->BusinessPartner->saveAll($this->data, array('validate' => 'first')) || (isset($business_partner) && !empty($business_partner))) {
					
					if (isset($this->data['Purchaser']['same']) && $this->data['Purchaser']['same']) {
						$purchaser = array(
							'Purchaser' => array(
								'name' => $this->data['BusinessPartner']['name'],
								'degree_before' => $this->data['BusinessPartner']['degree_before'],
								'first_name' => $this->data['BusinessPartner']['first_name'],
								'last_name' => $this->data['BusinessPartner']['last_name'],
								'degree_after' => $this->data['BusinessPartner']['degree_after'],
								'email' => $this->data['BusinessPartner']['email'],
								'phone' => $this->data['BusinessPartner']['phone'],
								'active' => true,
								'business_partner_id' => (isset($business_partner) && !empty($business_partner) ? $business_partner['BusinessPartner']['id'] : $this->BusinessPartner->id),
								'user_id' => $this->user['User']['id']
							),
							'Address' => array(
								'street' => $this->data['Address'][0]['street'],
								'number' => $this->data['Address'][0]['number'],
								'o_number' => $this->data['Address'][0]['o_number'],
								'city' => $this->data['Address'][0]['city'],
								'zip' => $this->data['Address'][0]['zip'],
								'region' => $this->data['Address'][0]['region'],
								'address_type_id' => 4
							)
						);
						
						if ($this->BusinessPartner->Purchaser->saveAll($purchaser)) {
							// pokud jsem naparoval odberatele na jiz existujiciho obchodniho partnera, ktery byl deaktivovan, musim ho zase aktivovat a priradit tomuto uzivateli
							if (isset($business_partner) && !empty($business_partner) && !$business_partner['BusinessPartner']['active']) {
								$business_partner['BusinessPartner']['active'] = true;
								$business_partner['BusinessPartner']['user_id'] = $this->user['User']['id'];
							}
							$datasource->commit($this->BusinessPartner);
							$this->Session->setFlash('Obchodní partner byl vytvořen včetně odběratele');
							$this->redirect(array('controller' => 'business_partners', 'action' => 'index'));
						} else {
							$datasource->rollback($this->BusinessPartner);
							$this->Session->setFlash('Nepodařilo se vytvořit odběratele k obchodnímu partnerovi. Zadejte jméno odběratele a jeho adresu');
						}
					} else {
						$datasource->commit($this->BusinessPartner);
						$this->Session->setFlash('Obchodní partner byl vytvořen');
						$this->redirect(array('controller' => 'business_partners', 'action' => 'index'));
					}
				} else {
					$datasource->rollback($this->BusinessPartner);
					$this->Session->setFlash('Obchodního partnera se nepodařilo vytvořit, opravte chyby ve formuláři a opakujte prosím akci');
				}
			} else {
				$this->Session->setFlash('Údaje o obchodním partnerovi byly doplněny ze systému Ares');
			}
		} else {
			if (isset($this->params['named']['data'])) {
				$data = unserialize(base64_decode($this->params['named']['data']));
				$this->data['BusinessPartner']['name'] = $data['ojm'];
				$this->data['Address'][0]['name'] = $data['ojm'];
				$this->data['BusinessPartner']['ico'] = $data['ico'];
				$address = explode(',', $data['jmn']);
				if (count($address) > 1) {
					$street = explode(' ', $address[count($address) - 1]);
					unset($address[count($address) - 1]);
					$this->data['Address'][0]['city'] = implode(', ', $address);
					$this->data['Address'][0]['number'] = $street[count($street) - 1];
					unset($street[count($street) - 1]);
					$this->data['Address'][0]['street'] = implode(' ', $street);
				} else {
					$street = explode(' ', $address[0]);
					$this->data['Address'][0]['number'] = $street[count($street) - 1];
					unset($street[count($street) - 1]);
					$this->data['Address'][0]['city'] = implode(' ', $street);
				}
			}
			$this->data['Purchaser']['same'] = false;	
		}
		
		$this->set('user', $this->user);
	}
	
	function user_ares_search() {
		$user_id = $this->user['User']['id'];
		$this->set('user_id', $user_id);
		
		if (isset($this->data)) {
			$iso_data = array();
			foreach ($this->data['BusinessPartner'] as $key => $item) {
				$iso_data['BusinessPartner'][$key] = iconv('utf-8', 'CP1250', $item);
			}

			$url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?jazyk=cz&obch_jm=' . urlencode($iso_data['BusinessPartner']['company']) . '&ico=' . $iso_data['BusinessPartner']['ico'] . '&cestina=cestina&obec=' . urlencode($iso_data['BusinessPartner']['city']) . '&k_fu=&maxpoc=' . $iso_data['BusinessPartner']['items'] . '&ulice=' . urlencode($iso_data['BusinessPartner']['street']) . '&cis_or=' . $iso_data['BusinessPartner']['number'] . '&cis_po=' . $iso_data['BusinessPartner']['number'] . '&setrid=' . $iso_data['BusinessPartner']['sort'] . '&pr_for=' . $iso_data['BusinessPartner']['law_form'] . '&nace=' . $iso_data['BusinessPartner']['cz_nace'] . '&xml=0&filtr=' . $iso_data['BusinessPartner']['filter'];
			if (!$ares_xml = download_url_like_browser($url)) {
				$this->Session->setFlash('Dokument se nepodařilo stáhnout.');
			} else {
	
				// mam vysledky z aresu, musim odlisit chybovy vysledky od regulernich a pokud jsou regulerni, tak je vypsat
				$dom = new DOMDocument('1.0');
				$dom->formatOutput = true;
				$dom->preserveWhiteSpace = false;
				libxml_use_internal_errors(true);
				if (!$dom->loadXML($ares_xml)) {
					die('dokument se nenaloudoval');
				}
				$domXPath = new DOMXPath($dom);
				
				$error = $domXPath->query('//dtt:R');
				// vystup obsahuje chybovou hlasku
				if ($error->length) {
					$flash = array();
					for ($i=0; $i<$error->length; $i++) {
						$flash []= $error->item($i)->nodeValue;
					}
					$this->Session->setFlash(implode('<br/>', $flash));
				} else {
					// uspech - musim vyparsovat data a predat k zobrazeni
					$result = $domXPath->query('//dtt:S');
					if ($result->length) {
						$search_results = array();
						foreach ($result as $r) {
							$search_result = array();
							$data = $r->childNodes;
							foreach ($data as $d) {
								switch ($d->nodeName) {
									case 'dtt:ico':
										$search_result['ico'] = $d->nodeValue;
										break;
									case 'dtt:pf':
										$search_result['pf'] = $d->nodeValue;
										break;
									case 'dtt:ojm':
										$search_result['ojm'] = $d->nodeValue;
										break;
									case 'dtt:jmn':
										$search_result['jmn'] = $d->nodeValue;
										break;
								}
							}
							$search_results []= $search_result;
						}
						$this->set('search_results', $search_results);
					} else {
						$this->Session->setFlash('Tohle by se nemělo vůbec ukázat');
					}
				}
			}
		}
	}
	
	function user_edit($id = null) {
		$redirect = $this->index_link;
		if (isset($this->params['named']['back_link'])) {
			$redirect = base64_decode($this->params['named']['back_link']);
			if ($array = unserialize($redirect)) {
				$redirect = $array;
			}
		}

		if (!$id) {
			$this->Session->setFlash('Není určen obchodní partner, kterého chcete upravovat');
			$this->redirect($redirect);
		}
		
		$business_partner = $this->BusinessPartner->find('first', array(
			'conditions' => array('BusinessPartner.id' => $id),
			'contain' => array(
				'Address' => array(
					'conditions' => array('Address.address_type_id' => 1)
				)
			)
		));
		
		if (empty($business_partner)) {
			$this->Session->setFlash('Zvolený obchodní partner neexistuje');
			$this->redirect($redirect);
		}
		
		// do leveho menu pridam polozku pro detaily partnera
		$this->left_menu_list[] = 'business_partner_detailed';
		$seat_address = $this->BusinessPartner->Address->find('first', array(
			'conditions' => array(
				'Address.business_partner_id' => $id,
				'Address.address_type_id' => 1
			)
		));
		
		$this->set(compact('business_partner', 'seat_address'));

		
		if (isset($this->data)) {
			
			// dogeneruju si nazev obchodniho partnera, pokud nemam zadany
			if (empty($this->data['BusinessPartner']['name']) && (!empty($this->data['BusinessPartner']['first_name']) || !empty($this->data['BusinessPartner']['last_name']))) {
				$name = array(
					$this->data['BusinessPartner']['degree_before'],
					$this->data['BusinessPartner']['first_name'],
					$this->data['BusinessPartner']['last_name'],
					$this->data['BusinessPartner']['degree_after']
				);
				$name = array_filter($name);
				$name = implode(' ', $name);
				$this->data['BusinessPartner']['name'] = $name;
			}
			
			if ($this->BusinessPartner->saveAll($this->data)) {
				$this->Session->setFlash('Obchodní partner byl upraven');
				$this->redirect($redirect);
			} else {
				$this->Session->setFlash('Obchodního partnera se nepodařilo upravit, opravte chyby ve formuláři a opakujte prosím akci');
			}
		} else {
			$this->data = $business_partner;
		}
	}
	
	function user_delete($id) {
		if (!$id) {
			$this->Session->setFlash('Není určen obchodní partner, kterého chcete smazat');
			$this->redirect($this->index_link);
		}
		
		$business_partner = $this->BusinessPartner->find('first', array(
			'conditions' => array('BusinessPartner.id' => $id),
			'contain' => array()
		));
		
		if (empty($business_partner)) {
			$this->Session->setFlash('Zvolený obchodní partner neexistuje');
			$this->redirect($this->index_link);
		}
		
		if ($this->BusinessPartner->delete($id)) {
			$this->Session->setFlash('Obchodní partner byl odstraněn');
		} else {
			$this->Session->setFlash('Obchodního partnera se nepodařilo odstranit');
		}
		$this->redirect($this->index_link);
	}
	
	function user_autocomplete_list() {
		$term = null;
		if ($_GET['term']) {
			$term = $_GET['term'];
		}

		echo $this->BusinessPartner->autocomplete_list($this->user, $term);
		die();
	}
	
	function address($id = null) {
		$result = array(
			'success' => false,
			'message' => null	
		);
		
		if (!$id) {
			$result['message'] = 'Není zadán obchodní partner, jehož adresu chcete získat';
		} else {
			$address = $this->BusinessPartner->Address->find('first', array(
				'conditions' => array(
					'Address.business_partner_id' => $id,
					'Address.address_type_id' => 1
				),
				'contain' => array(),
			));
			
			if (empty($address)) {
				$result['message'] = 'Obchodní partner nemá zadanou žádnou adresu';
			} else {
				$result['data'] = $address;
				$result['success'] = true;
			}
		}
		
		echo json_encode($result);
		die();
	}
	
	function contact_people($id = null) {
		$res = array(
			'success' => false,
			'message' => '',
			'data' => null
		);
		
		if (!$id) {
			$res['message'] = 'Není zadán obchodní partner, jehož kontaktní osoby chcete vypsat';
		} else {
			$contact_people = json_decode($this->BusinessPartner->Purchaser->ContactPerson->autocomplete_list($this->user, null, $id));

			if ($contact_people) {
				$res['data'] = $contact_people;
				$res['success'] = true;
			} else {
				$res['message'] = 'Nepodařilo se zjistit kontaktní osoby daného obchodního partnera';
			}
		}
		
		echo json_encode($res); die();
	}
}
?>
