<?php
class ProductsTransaction extends AppModel {
	var $name = 'ProductsTransaction';
	
	var $actsAs = array('Containable');
	
	var $belongsTo = array(
		'Product',
		'Transaction',
		'Sale' => array(
			'foreignKey' => 'transaction_id'
		),
		'DeliveryNote' => array(
			'foreignKey' => 'transaction_id'
		)
	);
	
	var $virtualFields = array(
		'abs_quantity' => 'ABS(`ProductsTransaction`.`quantity`)',
		'total_price' => '`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`',
		'abs_total_price' => 'ABS(`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`)',
	);
	
	var $validate = array(
		'quantity' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Zadejte množství zboží'
			),
			'notZero' => array(
				'rule' => array('comparison', 'not equal', 0),
				'message' => 'Zadejte množství zboží'
			)
		),
		'product_id' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
				'message' => 'Vyberte zboží'
			)
		)
	);
	
	var $active = array();
	
	var $deleted = null;
	
	function afterSave($created) {
		$data = $this->data;
		// pokud vkladam novou polozku
		if ($created) {
			// najdu si produkt, ke kteremu se vztahuje
			$product = $this->Product->find('first', array(
				'conditions' => array('Product.id' => $data['ProductsTransaction']['product_id']),
				'contain' => array(),
				'fields' => array('Product.price')
			));

			if (empty($product)) {
				return false;
			} else {
				// vyplnim si cenu a marzi produktu v dobe vytvoreni polozky
				$this->data['ProductsTransaction']['unit_price'] = $product['Product']['price'];
				$this->save($this->data);
			}
			
			// musim upravit stav polozek ve skladu odberatele
			$store_item = $this->Transaction->Purchaser->StoreItem->find('first', array(
				'conditions' => array(
					'StoreItem.product_id' => $data['ProductsTransaction']['product_id'],
					'StoreItem.purchaser_id' => $data['ProductsTransaction']['purchaser_id']
				),
				'contain' => array(),
				'fields' => array('StoreItem.id', 'StoreItem.quantity')
			));
			
			if (empty($store_item)) {
				$store_item = array(
					'StoreItem' => array(
						'product_id' => $data['ProductsTransaction']['product_id'],
						'quantity' => $data['ProductsTransaction']['quantity'],
						'purchaser_id' => $data['ProductsTransaction']['purchaser_id']
					)
				);
			} else {
				$store_item['StoreItem']['quantity'] += $data['ProductsTransaction']['quantity'];					
			}
			
			$this->Transaction->Purchaser->StoreItem->create();
			$this->Transaction->Purchaser->StoreItem->save($store_item);
			
			$this->active[] = $this->id;
		}

		return true;
	}
	
	// musim si zapamatovat, co mazu, abych to mohl po smazani odecist ze skladu odberatele
	function beforeDelete() {
		$this->deleted = $this->find('first', array(
			'conditions' => array('ProductsTransaction.id' => $this->id),
			'contain' => array(
				'Transaction' => array(
					'fields' => array('Transaction.id', 'Transaction.business_partner_id'),
					'TransactionType' => array(
						'fields' => array('TransactionType.subtract')
					)
				)
			)
		));
		
		return true;
	}
	
	function afterDelete() {
		// ze skladu odberatele odectu, co jsem smazal z transakce
		$store_item = $this->Transaction->Purchaser->StoreItem->find('first', array(
			'conditions' => array(
				'StoreItem.purchaser_id' => $this->deleted['Transaction']['purchaser_id'],
				'StoreItem.product_id' => $this->deleted['ProductsTransaction']['product_id']
			),
			'contain' => array(),
			'fields' => array('StoreItem.id', 'StoreItem.quantity')
		));
		
		if (empty($store_item)) {
			$this->Transaction->Purchaser->StoreItem->create();
			$store_item = array(
				'StoreItem' => array(
					'purchaser_id' => $this->deleted['Transaction']['purchaser_id'],
					'product_id' => $this->deleted['ProductsTransaction']['product_id'],
					'quantity' => 0
				)
			);
		}
	
		$store_item['StoreItem']['quantity'] -= $this->deleted['ProductsTransaction']['quantity'];
		$this->Transaction->Purchaser->StoreItem->save($store_item);
	}

}
