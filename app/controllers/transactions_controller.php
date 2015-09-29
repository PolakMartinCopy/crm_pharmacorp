<?php
class TransactionsController extends AppController {
	var $name = 'Transactions';
	
	var $left_menu_list = array('transactions');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->set('left_menu_list', $this->left_menu_list);
		$this->set('active_tab', 'transactions');
		
		$this->Auth->allow('repair');
	}	
	
	function user_index() {
		// model, ze ktereho metodu volam
		$model = $this->modelNames[0];
		$this->set('model', $model);
		
		if (isset($this->params['named']['reset'])) {
			$this->Session->delete('Search.' . $model . 'Form');
			$this->redirect(array('controller' => $this->params['controller'], 'action' => 'index'));
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

		// aby mi to radilo i podle poli modelu, ktere nemam primo navazane na delivery note, musim si je naimportovat
		App::import('Model', 'Product');
		$this->$model->Product = new Product;
		App::import('Model', 'Unit');
		$this->$model->Unit = new Unit;

		$this->$model->virtualFields['purchaser_name'] = $this->$model->Purchaser->virtualFields['name'];
		$this->paginate = array(
			'conditions' => $conditions,
			'limit' => 30,
			'contain' => array(),
			'joins' => array(
				array(
					'table' => 'products_transactions',
					'alias' => 'ProductsTransaction',
					'type' => 'left',
					'conditions' => array($model . '.id = ProductsTransaction.transaction_id')
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
					'conditions' => array('Purchaser.id = ' . $model . '.purchaser_id')	
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
					'conditions' => array($model . '.transaction_type_id = TransactionType.id')
				),
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'left',
					'conditions' => array($model . '.user_id = User.id')
				)
			),
			'fields' => array(
				$model . '.id',
				$model . '.date',
				$model . '.code',
				$model . '.transaction_type_id',
				$model . '.abs_quantity',
				$model . '.abs_total_price',
				$model . '.total_price',
				$model . '.quantity',
				$model . '.purchaser_name',

				'ProductsTransaction.id',
				'ProductsTransaction.unit_price',
	
				'Product.id',
				'Product.name',
				'Product.vzp_code',
				'Product.group_code',
					
				'Purchaser.id',
							
				'BusinessPartner.id',
				'BusinessPartner.name',
							
				'Unit.id',
				'Unit.shortcut',
					
				'TransactionType.id',
				'TransactionType.subtract',
			),
			'order' => array(
				$model . '.created' => 'desc',
			)
		);
		$transactions = $this->paginate();
		unset($this->$model->virtualFields['purchaser_name']);
		$this->set('transactions', $transactions);
		
		$find = $this->paginate;
		unset($find['limit']);
		unset($find['fields']);
		$this->set('find', $find);

		// nastaveni textu (pohled je pro dodaci listy i prodeje stejny)
		$header = 'pohyby';
		if ($model == 'DeliveryNote') {
			$header = 'dodací listy';
		} elseif ($model == 'Sale') {
			$header = 'poukazy';
		}
		$this->set('header', $header);
		
		$export_fields = $this->$model->export_fields();
		$this->set('export_fields', $export_fields);
		
		// seznam uzivatelu pro select ve filtru
		$users = $this->$model->User->users_filter_list($this->user['User']['user_type_id'], $this->user['User']['id']);
		$this->set('users', $users);
		
		$this->render('/transactions/user_index');
	}
	
	function user_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou transakci chcete upravovat');
			$this->redirect(array('action' => 'index'));
		}

		$model = $this->modelNames[0];
		$this->set('model',  $model);
		
		$conditions = array($model . '.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}

		$transaction = $this->$model->find('first', array(
			'conditions' => $conditions,
			'contain' => array(
				'ProductsTransaction' => array(
					'Product' => array(
						'fields' => array('Product.id', 'Product.info')
					),
					'fields' => array(
						'ProductsTransaction.id', 'ProductsTransaction.quantity'
					)
				),
				'Purchaser' => array(
					'fields' => array('Purchaser.id', 'Purchaser.name')
				),
				'TransactionType' => array(
					'fields' => array('TransactionType.subtract')
				)
			),
			'fields' => array($model . '.id', $model . '.date', $model . '.time', $model . '.purchaser_id', $model . '.transaction_type_id')
		));
		
		if (empty($transaction)) {
			$this->Session->setFlash('Transakce, kterou chcete upravovat, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
		
		$this->set('transaction', $transaction);
	
		if (isset($this->data)) {
			if (isset($this->data['ProductsTransaction'])) {
				foreach ($this->data['ProductsTransaction'] as $index => &$products_transaction) {
					if (empty($products_transaction['product_id']) && empty($products_transaction['quantity'])) {
						unset($this->data['ProductsTransaction'][$index]);
					} else {
						$products_transaction['purchaser_id'] = $this->data[$model]['purchaser_id'];
						if ($products_transaction['subtract']) {
							$products_transaction['quantity'] =  -$products_transaction['quantity'];
						}
					}
				}
				if (empty($this->data['ProductsTransaction'])) {
					$this->Session->setFlash('Transakce neobsahuje žádné produkty a nelze ji proto uložit');
				} else {
					// nejprve musim zjistit, jestli jsou vkladana data validni a saveall probehne v poradku
					if ($this->$model->saveAll($this->data, array('validate' => 'only'))) {
		/*				// pred updatovani transakce musim smazat vsechny transakce, ktere po teto nasleduji, pak ulozit tuto transakci a nasledne znovu vlozit vsechny smazane transakce
						// plati pro transakce daneho uzivatele
						App::import('Model', 'Transaction');
						$this->Transaction = new Transaction;
						// podivam se, jestli mam v systemu pro daneho uzivatele transakce, ktere vlozeny s datem PO datu vlozeni teto transakce, tyto transakce si zapamatuju v property modelu
						$date = $this->data[$model]['date'];
						$date = explode('.', $date);
						$date = $date[2] . '-' . $date[1] . '-' . $date[0];
						$date_time = $date . ' ' . $this->data[$model]['time']['hour'] . ':' . $this->data[$model]['time']['min'] . ':00';
						$future_transactions = $this->Transaction->find('all', array(
							'conditions' => array(
								'CONCAT(Transaction.date, " ", Transaction.time) >' => $date_time,
								'Transaction.purchaser_id' => $this->data[$model]['purchaser_id'],
								'Transaction.id !=' => $this->data[$model]['id']
							),
							'contain' => array(
								'ProductsTransaction' => array(
									'fields' => array('ProductsTransaction.id', 'ProductsTransaction.created', 'ProductsTransaction.product_id', 'ProductsTransaction.transaction_id', 'ProductsTransaction.quantity', 'ProductsTransaction.unit_price', 'ProductsTransaction.product_margin')
								)
							),
							'fields' => array('Transaction.id', 'Transaction.created', 'Transaction.code', 'Transaction.business_partner_id', 'Transaction.date', 'Transaction.time', 'Transaction.transaction_type_id', 'Transaction.user_id'),
							'order' => array(
								'Transaction.date' => 'asc',
								'Transaction.time' => 'asc'
							)
						));
						
						foreach ($future_transactions as &$transaction) {
							foreach ($transaction['ProductsTransaction'] as &$products_transaction) {
								$products_transaction['business_partner_id'] = $transaction['Transaction']['business_partner_id'];
							}
						}
						
						// smazu transakce po teto transakci, tim se mi prepocita sklad odberatele
						foreach ($future_transactions as $future_transaction) {
							$this->Transaction->delete($future_transaction['Transaction']['id']);
						}
*/						
						if ($this->$model->saveAll($this->data)) {
							// musim smazat vsechny polozky, ktere jsou v systemu pro dany zaznam, ale nejsou uz aktivni podle editace (byly odstraneny ze seznamu)
							$to_del_pts = $this->$model->ProductsTransaction->find('all', array(
								'conditions' => array(
									'ProductsTransaction.transaction_id' => $this->$model->id,
									'ProductsTransaction.id NOT IN (' . implode(',', $this->$model->ProductsTransaction->active) . ')'
								),
								'contain' => array(),
								'fields' => array('ProductsTransaction.id')
							));
							foreach ($to_del_pts as $to_del_pt) {
								$this->$model->ProductsTransaction->delete($to_del_pt['ProductsTransaction']['id']);
							}
/*	
							// natahnu si delivery note model, abych mohl volat metodu pro pregenerovani pdf DL
							App::import('Model', 'DeliveryNote');
							$this->DeliveryNote = new DeliveryNote;
							
							// pokud jsem updatoval dodaci list
							if (isset($this->data[$model]['transaction_type_id']) && $this->data[$model]['transaction_type_id'] == 1) {
								// vytvorim pdf dodaciho listu
								$this->DeliveryNote->pdf_generate($this->data[$model]['id']);
							}
							
							foreach ($future_transactions as $future_transaction) {
								if ($this->Transaction->saveAll($future_transaction)) {
									if ($future_transaction['Transaction']['transaction_type_id'] == 1) {
										// vytvorim pdf dodaciho listu
										$this->DeliveryNote->pdf_generate($future_transaction['Transaction']['id']);
									}
								}
							}
*/
							$this->Session->setFlash('Transakce byla uložena');
							if (isset($this->params['named']['purchaser_id'])) {
								// specifikace tabu, ktery chci zobrazit, pokud upravuju transakci z detailu odberatele
								// defaultne nastavim tab pro DeliveryNote
								$tab = 10;
								if ($model == 'Sale') {
									$tab = 11;
								}
								$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => $tab));
							} else {
								$this->redirect(array('action' => 'index'));
							}
						}
					} else {
						foreach ($this->data['ProductsTransaction'] as &$products_transaction) {
							if ($products_transaction['subtract']) {
								$products_transaction['quantity'] =  -$products_transaction['quantity'];
							}
						}
						$this->Session->setFlash('Transakci se nepodařilo uložit, opravte chyby ve formuláři a opakujte prosím akci');
					}
				}
			} else {
				$this->Session->setFlash('Transakce neobsahuje žádné produkty a nelze ji proto uložit');
			}
		} else {
			$transaction[$model]['purchaser_name'] = $transaction['Purchaser']['name'];
			foreach ($transaction['ProductsTransaction'] as &$products_transaction) {
				$products_transaction['product_name'] = $products_transaction['Product']['info'];
				$products_transaction['subtract'] = $transaction['TransactionType']['subtract'];
				if ($transaction['TransactionType']['subtract']) {
					$products_transaction['quantity'] = -$products_transaction['quantity'];
				}
			}
			$transaction[$model]['date'] = db2cal_date($transaction[$model]['date']);
			$this->data = $transaction;
		}
		
		$this->render('/transactions/user_edit');
	}
	
	function user_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Není zadáno, kterou transakci chcete smazat');
			$this->redirect(array('action' => 'index'));
		}
		$model = $this->modelNames[0];
		
		$conditions = array($model . '.id' => $id);
		if ($this->user['User']['user_type_id'] == 3) {
			$conditions['Purchaser.user_id'] = $this->user['User']['id'];
		}

		$transaction = $this->$model->find('first', array(
			'conditions' => $conditions,
			'contain' => array(
				'Purchaser' => array(
					'fields' => array('Purchaser.id', 'Purchaser.name')
				)
			),
			'fields' => array($model . '.id', $model . '.date', $model . '.time', $model . '.purchaser_id')
		));

		if (empty($transaction)) {
			$this->Session->setFlash('Transakce, kterou chcete smazat, neexistuje');
			$this->redirect(array('action' => 'index'));
		}
/*		
		// pred smazanim transakce musim smazat vsechny transakce, ktere po teto nasleduji, pak ulozit tuto transakci a nasledne znovu vlozit vsechny smazane transakce
		// plati pro transakce daneho uzivatele
		App::import('Model', 'Transaction');
		$this->Transaction = new Transaction;
		// podivam se, jestli mam v systemu pro daneho uzivatele transakce, ktere vlozeny s datem PO datu vlozeni teto transakce, tyto transakce si zapamatuju v property modelu
		$date_time = $transaction[$model]['date'] . ' ' . $transaction[$model]['time'];

		$future_transactions = $this->Transaction->find('all', array(
			'conditions' => array(
				'CONCAT(Transaction.date, " ", Transaction.time) >' => $date_time,
				'Transaction.purchaser_id' => $transaction[$model]['purchaser_id'],
				'Transaction.id !=' => $transaction[$model]['id']
			),
			'contain' => array(
				'ProductsTransaction' => array(
					'fields' => array('ProductsTransaction.id', 'ProductsTransaction.created', 'ProductsTransaction.product_id', 'ProductsTransaction.transaction_id', 'ProductsTransaction.quantity', 'ProductsTransaction.unit_price', 'ProductsTransaction.product_margin')
				)
			),
			'fields' => array('Transaction.id', 'Transaction.created', 'Transaction.code', 'Transaction.business_partner_id', 'Transaction.date', 'Transaction.time', 'Transaction.transaction_type_id', 'Transaction.user_id'),
			'order' => array(
				'Transaction.date' => 'asc',
				'Transaction.time' => 'asc'
			)
		));

		foreach ($future_transactions as &$transaction) {
			foreach ($transaction['ProductsTransaction'] as &$products_transaction) {
				$products_transaction['business_partner_id'] = $transaction['Transaction']['business_partner_id'];
			}
		}
			
		// smazu transakce po teto transakci, tim se mi prepocita sklad odberatele
		foreach ($future_transactions as $future_transaction) {
			$this->Transaction->delete($future_transaction['Transaction']['id']);
		}
*/
		if ($this->$model->delete($id)) {
/*
			App::import('Model', 'DeliveryNote');
			$this->DeliveryNote = new DeliveryNote;
			
			foreach ($future_transactions as $future_transaction) {
				if ($this->Transaction->saveAll($future_transaction)) {
					if ($future_transaction['Transaction']['transaction_type_id'] == 1) {
						// vytvorim pdf dodaciho listu
						$this->DeliveryNote->pdf_generate($future_transaction['Transaction']['id']);
					}
				}
			}
*/	
			$this->Session->setFlash('Transakce byla odstraněna');
		} else {
			$this->Session->setFlash('Transakci se nepodařilo odstranit, opakujte prosím akci');
		}
		if (isset($this->params['named']['purchaser_id'])) {
			// nastaveni tabu pri presmerovani na detail odberatele, defaultne DeliveryNote
			$tab = 10;
			if ($model == 'Sale') {
				$tab = 11;
			}
			
			$this->redirect(array('controller' => 'purchasers', 'action' => 'view', $this->params['named']['purchaser_id'], 'tab' => $tab));
		} else {
			$this->redirect(array('action' => 'index'));
		}
	}
}
