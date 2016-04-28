<?php
class AppModel extends Model {
	var $export_file = 'files/export.csv';
	
//	var $useDbConfig = 'test';
	
	// kontroluje, jestli se jedna o obycejneho uzivatele a jestli nevyzaduje pristup
	// k datum jineho uzivatele
	function checkUser($user, $checked_id) {
		if ($user['User']['user_type_id'] == 3) {
			if ($checked_id != $user['User']['id']) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 
	 * Z data ve formatu array udela retezec pro ulozeni do db
	 * @param array $date
	 */
	function built_date($date) {
		if (strlen($date['month']) == 1) {
			$date['month'] = '0' . $date['month'];
		}
		if (strlen($date['day']) == 1) {
			$date['day'] = '0' . $date['day'];
		}
		return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
	}
	
	/**
	 * 
	 * z data ve stringu udela pole
	 * @param string $date
	 */
	function unbuilt_date($date) {
		$date = explode('-', $date);
		return array('day' => $date[2], 'month' => $date[1], 'year' => $date[0]);
	}
	
	/**
	 * 
	 * provadi export dat, pouzije zadany find a data zapise do xls
	 * @param array $find
	 */
	function xls_export($find, $export_fields, $virtualFields = array()) {
		// pole kde jsou data typu datetim
		$datetime_fields = array(
/*			'BusinessSession.date',
			'BusinessSession.created',
			'Imposition.created',
			'Offer.created'*/
		);
		
		// pole kde jsou data typu date
		$date_fields = array(
/*			'Solution.accomplishment_date',
			'Cost.date',
			'DeliveryNote.date',
			'Sale.date',
			'Transaction.date'*/
		);
		
		$month_fields = array(
			'["Contract"]["month"]'
		);
		
		// exportuju udaj o tom, ktera pole jsou soucasti vystupu
		$find['fields'] = Set::extract('/field', $export_fields);

		// vyhledam data podle zadanych kriterii
		if (!empty($virtualFields)) {
			foreach ($virtualFields as  $key => $value) {
				$this->virtualFields[$key] = $value;
			}
		}
		$data = $this->find('all', $find);

		$file = fopen($this->export_file, 'w');

		// zjistim aliasy, pod kterymi se vypisuji atributy v csv souboru
		$aliases = Set::extract('/alias', $export_fields);

		$line = implode(';', $aliases);
		// do souboru zapisu hlavicku csv (nazvy sloupcu)
		fwrite($file, iconv('utf-8', 'windows-1250', $line . "\r\n"));

		$positions = Set::extract('/position', $export_fields);
		// do souboru zapisu data (radky vysledku)
		foreach ($data as $item) {

			$line = '';
			$results = array();
			foreach ($positions as $index => $position) {
				$expression = '$item' . $position;
				$escape_quotes = true;
				if (array_key_exists('escape_quotes', $export_fields[$index])) {
					$escape_quotes = $export_fields[$index]['escape_quotes'];
				}
				if ($escape_quotes) {
					$expression = str_replace('"', '\'', $expression);
				}

				eval("\$result = ". $expression . ";");
				
				if (in_array($position, $month_fields)) {
					$months = months();
					$result = $months[$result];
				} else {
					// prevedu sloupce s datetime
					$result = preg_replace('/^(\d{4})-(\d{2})-(\d{2}) (.+)$/', '$3.$2.$1 $4', $result);
					// prevedu sloupce s datem
					$result = preg_replace('/^(\d{4})-(\d{2})-(\d{2})$/', '$3.$2.$1', $result);
					// nahradim desetinnou tecku carkou
					$result = preg_replace('/^(-?\d+)\.(\d+)$/', '$1,$2', $result);
					// odstranim nove radky
					$result = str_replace("\r\n", ' ', $result);
				}
				$results[] = $result;
			}
			$line = implode(';', $results);

			// ulozim radek
			fwrite($file, iconv('utf-8', 'windows-1250', $line . "\n"));
		}

		fclose($file);
		return true;
	}
	
	function getFieldValue($id, $field) {
		$item = $this->find('first', array(
				'conditions' => array('id' => $id),
				'contain' => array(),
				'fields' => array($field)
		));
	
		if (empty($item)) {
			return false;
		}
		return $item[$this->name][$field];
	}
	
	function getIdByField($value, $field) {
		$item = $this->find('first', array(
				'conditions' => array($field => $value),
				'contain' => array(),
				'fields' => array('id')
		));
		if (empty($item)) {
			return false;
		}
		return $item[$this->name]['id'];
	}
	
	function getItemById($id) {
		$item = $this->find('first', array(
				'conditions' => array('id' => $id),
				'contain' => array()
		));
		return $item;
	}
	
	function setAttribute($id, $attName, $attValue) {
		$save = array(
				$this->name => array(
						'id' => $id,
						$attName => $attValue
				)
		);
		return $this->save($save);
	}
	
	function getTotal($conditions, $contain, $joins, $options) {
		$total = $this->find('first', array(
			'conditions' => $conditions,
			'contain' => $contain,
			'joins' => $joins,
			'fields' => array('SUM(' . $options['col_expr'] . ') AS ' . $options['col_name'])
		));
		
		return $total[0][$options['col_name']];
	}
}
