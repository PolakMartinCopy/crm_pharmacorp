<?php
class BusinessSession extends AppModel {
	var $name = 'BusinessSession';
	
	var $actsAs = array('Containable');
	
	var $transactional = true;
	
	var $belongsTo = array(
		'User', // vedouci obchodniho jednani
		'Purchaser',
		'BusinessSessionState',
		'BusinessSessionType',
	);
	
	var $hasMany = array(
		'BusinessSessionsUser' => array('dependent' => true), // prizvany obchodnik na jednani
		'BusinessSessionsContactPerson' => array('dependent' => true), // kontaktni osoba prizvana na jednani
		'BusinessSessionsCost' => array('dependent' => true), // naklady
		'Contract'
	);
	
	var $validate = array(
		'user_id' => array(
			'rule' => 'numeric',
			'allowEmpty' => false,
			'message' => 'Není zvolen odpovědný uživatel'
		),
		'date' => array(
			'rule' => 'notEmpty',
			'allowEmpty' => false,
			'message' => 'Termín konání obchodní schůzky nesmí zůstat prázdný'
		),
		'business_session_type_id' => array(
			'rule' => 'numeric',
			'allowEmpty' => false,
			'message' => 'Není zvolen typ obchodního jednání'
		),
		'business_session_state_id' => array(
			'rule' => 'numeric',
			'allowEmpty' => false,
			'message' => 'Není zvolen stav obchodního jednání'
		),
		'purchaser_id' => array(
			'rule' => 'numeric',
			'allowEmpty' => false,
			'message' => 'Není zvolen obchodní partner pro jednání'
		)
	);
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
			$this->export_fields = $export_fields = array(
			array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
			array('field' => $this->Purchaser->virtualFields['name'], 'position' => '[0][\'' .  $this->Purchaser->virtualFields['name'] . '\']', 'alias' => 'Purchaser.name', 'escape_quotes' => false),
			array('field' => 'BusinessSession.id', 'position' => '["BusinessSession"]["id"]', 'alias' => 'BusinessSession.id'),
			array('field' => 'BusinessSession.date', 'position' => '["BusinessSession"]["date"]', 'alias' => 'BusinessSession.date'),
			array('field' => 'BusinessSession.end_time', 'position' => '["BusinessSession"]["end_time"]', 'alias' => 'BusinessSession.end_time'),
			array('field' => 'BusinessSession.created', 'position' => '["BusinessSession"]["created"]', 'alias' => 'BusinessSession.created'),
			array('field' => 'BusinessSessionType.name', 'position' => '["BusinessSessionType"]["name"]', 'alias' => 'BusinessSessionType.name'),
			array('field' => 'BusinessSessionState.name', 'position' => '["BusinessSessionState"]["name"]', 'alias' => 'BusinessSessionState.name'),
			array('field' => 'CONCAT(User.last_name, " ", User.first_name) AS full_name', 'position' => '[0]["full_name"]', 'alias' => 'User.fullname'),
			array('field' => 'SUM(Cost.amount) AS total_amount', 'position' => '[0]["total_amount"]', 'alias' => 'Cost.total_amount')
		);
	}
	
	function getCosts($business_session) {
		$costs = 0;
		
		foreach ($business_session['Cost'] as $cost) {
			$costs += $cost['amount'];
		}
		
		return $costs;
	}
	
	function do_form_search($conditions, $data) {

		if ( !empty($data['Purchaser']['name']) ){
			$conditions[] = 'Purchaser.name LIKE \'%%' . $data['Purchaser']['name'] . '%%\'';
		}
		
		if ( !empty($data['BusinessSession']['date_from']) ){
			$date_from = explode('.', $data['BusinessSession']['date_from']);
			$date_from = $date_from[2] . '-' . $date_from[1] . '-' . $date_from[0] . ' 00:00:00';
			$conditions[] = 'BusinessSession.date > \'' . $date_from . '\'';
		}
		
		if ( !empty($data['BusinessSession']['date_to']) ){
			$date_to = explode('.', $data['BusinessSession']['date_to']);
			$date_to = $date_to[2] . '-' . $date_to[1] . '-' . $date_to[0] . ' 00:00:00';
			$conditions[] = 'BusinessSession.date < \'' . $date_to . '\'';
		}
		
		if ( !empty($data['BusinessSession']['created_from']) ){
			$created_from = explode('.', $data['BusinessSession']['created_from']);
			$created_from = $created_from[2] . '-' . $created_from[1] . '-' . $created_from[0] . ' 00:00:00';
			$conditions[] = 'BusinessSession.created > \'' . $created_from . '\'';
		}
		
		if ( !empty($data['BusinessSession']['created_to']) ){
			$created_to = explode('.', $data['BusinessSession']['created_to']);
			$created_to = $created_to[2] . '-' . $created_to[1] . '-' . $created_to[0] . ' 00:00:00';
			$conditions[] = 'BusinessSession.created < \'' . $created_to . '\'';
		}
		
		
		if ( !empty($data['BusinessSession']['business_session_type_id']) ){
			$conditions[] = 'BusinessSession.business_session_type_id = \'' . $data['BusinessSession']['business_session_type_id'] . '\'';
		}
		
		if ( !empty($data['BusinessSession']['description']) ){
			$conditions[] = 'BusinessSession.description LIKE \'%%' . $data['BusinessSession']['description'] . '%%\'';
		}
		if (!empty($data['BusinessSession']['user_id'])) {
			$conditions[] = 'BusinessSession.user_id = ' . $data['BusinessSession']['user_id'];
		}
		
		return $conditions;
	}
	
	function basic_query($conditions) {
		// sestavim query - musim spojit obchodni jednani, kde je uzivatel zodpovedny s temi, kde je uzivatel prizvany
		$conditions = implode(' AND ', $conditions);
		if (empty($conditions)) {
			$conditions = '1=1';
		}
		
		$query = '
		SELECT DISTINCT BusinessSession.id
		FROM
			business_sessions AS BusinessSession LEFT JOIN
			business_sessions_users AS BusinessSessionsUser ON (BusinessSession.id = BusinessSessionsUser.business_session_id) LEFT JOIN
			purchasers AS Purchaser ON (Purchaser.id = BusinessSession.purchaser_id) LEFT JOIN
			business_partners AS BusinessPartner ON (Purchaser.business_partner_id = BusinessPartner.id)
		WHERE ' . $conditions;

		return $query;
	}
	
	function paginate($conditions, $fields, $order, $limit = 20, $page = 1, $recursive, $extra) {
		$contain = $extra['contain'];

		$query = $this->basic_query($conditions);
		
		$business_session_ids = $this->query($query);
		$business_session_ids = Set::extract('/BusinessSession/id', $business_session_ids);
		
		$business_sessions = $this->find('all', array(
			'conditions' => array('BusinessSession.id' => $business_session_ids),
			'contain' => $contain,
			'order' => $order,
			'fields' => $fields,
			'limit' => $limit,
			'group' => 'BusinessSession.id',
			'joins' => array(
				array(
					'table' => 'costs',
					'alias' => 'Cost',
					'type' => 'LEFT',
					'conditions' => array(
						'Cost.business_session_id = BusinessSession.id'
					)
				)
			),
			'page' => $page
		));
		
		return $business_sessions;
	}
	
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$query = $this->basic_query($conditions);
		
		return count($this->query($query));
	}
	
	function xls_export($find, $export_fields) {
		$query = $this->basic_query($find['conditions']);
		
		$business_session_ids = $this->query($query);
		$business_session_ids = Set::extract('/BusinessSession/id', $business_session_ids);

		$find['conditions'] = array('BusinessSession.id' => $business_session_ids);
		$find['group'] = 'BusinessSession.id';
		$find['joins'] = array(
			array(
				'table' => 'costs',
				'alias' => 'Cost',
				'type' => 'LEFT',
				'conditions' => array(
					'Cost.business_session_id = BusinessSession.id'
				)
			)
		);
			
		// pole kde jsou data typu datetim
		$datetime_fields = array(
			'BusinessSession.date',
			'BusinessSession.created',
			'Imposition.created',
			'Offer.created'
		);
		
		// pole kde jsou data typu date
		$date_fields = array(
			'Imposition.accomplishment_date',
			'Cost.date'
		);
		
		// exportuju udaj o tom, ktera pole jsou soucasti vystupu
		$find['fields'] = Set::extract('/field', $export_fields);

		$data = $this->find('all', $find);

		$file = fopen($this->export_file, 'w');

		// zjistim aliasy, pod kterymi se vypisuji atributy v csv souboru
		$aliases = Set::extract('/alias', $export_fields);
		
		// rozdelim datetime a date pole zvlast do sloupcu den, mesic, rok
		$res_aliases = array();
		foreach ($aliases as $alias) {
			if (in_array($alias, $datetime_fields)) {
				$res_aliases[] = $alias . '_day';
				$res_aliases[] = $alias . '_month';
				$res_aliases[] = $alias . '_year';
				$res_aliases[] = $alias . '_time';
			} elseif (in_array($alias, $date_fields)) {
				$res_aliases[] = $alias . '_day';
				$res_aliases[] = $alias . '_month';
				$res_aliases[] = $alias . '_year';
			} else {
				$res_aliases[] = $alias;
			}
		}
		$aliases = $res_aliases;

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
				// rozdelim datetime zvlast na sloupce den, mesic, rok
				if (preg_match('/(....)-(..)-(..) (.+)/', $result, $matches)) {
					$results[] = $matches[3];
					$results[] = $matches[2];
					$results[] = $matches[1];
					$results[] = $matches[4];
				// rozdelim date zvlast na sloupce den, mesic, rok
				} elseif (preg_match('/(....)-(..)-(..)/', $result, $matches)) {
					$results[] = $matches[3];
					$results[] = $matches[2];
					$results[] = $matches[1];
				} else {
					if ($position == '[0]["total_amount"]' && empty($result)) {
						$result = 0;
					}
					$results[] = $result;
				}
			}
			
			$line = implode(';', $results);
			// ulozim radek
			fwrite($file, iconv('utf-8', 'windows-1250', $line . "\n"));
		}

		fclose($file);
		return true;
	}
	
	function cake2fullcalendar($business_sessions) {
		$events = array();
		if (!empty($business_sessions)) {
			foreach ($business_sessions as $business_session) {
				$title = $business_session['BusinessPartner']['name'] . ' - ' . $business_session['BusinessSession']['purchaser_name'];
				list($start_date, $start_time) = explode(' ', $business_session['BusinessSession']['date']);
				list($start_year, $start_month, $start_day) = explode('-', $start_date);
				list($start_hour, $start_min, $pom) = explode(':', $start_time);
				
				
				$end_hour = null;
				$end_time = null;
				if (!empty($business_session['BusinessSession']['end_time'])) {
					list($end_hour, $end_time, $pom) = explode(':', $business_session['BusinessSession']['end_time']);
				}
				$events[] = array(
					'id' => $business_session['BusinessSession']['id'],
					'title' => $title,
					'start_year' => $start_year,
					'start_month' => $start_month,
					'start_day' => $start_day,
					'start_hour' => $start_hour,
					'start_min' => $start_min,
					'end_year' => $start_year,
					'end_month' => $start_month,
					'end_day' => $start_day,
					'end_hour' => $end_hour,
					'end_min' => $end_time,
					'all_day' => false
				);
			}
		}
		return $events;
	}
}
