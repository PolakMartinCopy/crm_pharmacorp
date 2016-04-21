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
	
	var $virtualFields = array('short_desc' => 'LEFT(BusinessSession.description, 150)');
	
	var $export_file = 'files/business_sessions.csv';
	
	var $export_joins = array(
		array(
			'table' => 'business_sessions_costs',
			'alias' => 'BusinessSessionsCost',
			'type' => 'LEFT',
			'conditions' => array('BusinessSessionsCost.business_session_id = BusinessSession.id')
		),
		array(
			'table' => 'cost_types',
			'alias' => 'CostType',
			'type' => 'LEFT',
			'conditions' => array('CostType.id = BusinessSessionsCost.cost_type_id')
		),
		array(
			'table' => 'contracts',
			'alias' => 'Contract',
			'type' => 'LEFT',
			'conditions' => array('BusinessSession.id = Contract.business_session_id')
		),
		array(
			'table' => 'contact_people',
			'alias' => 'ContactPerson',
			'type' => 'LEFT',
			'conditions' => array('Contract.contact_person_id = ContactPerson.id')
		)
	);
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
			$this->export_fields = array(
			array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
			array('field' => $this->User->full_name . ' AS full_name', 'position' => '[0]["full_name"]', 'alias' => 'User.fullname'),
			array('field' => $this->Purchaser->virtualFields['name'], 'position' => '[0][\'' .  $this->Purchaser->virtualFields['name'] . '\']', 'alias' => 'Purchaser.name', 'escape_quotes' => false),
			array('field' => 'BusinessSession.id', 'position' => '["BusinessSession"]["id"]', 'alias' => 'BusinessSession.id'),
			array('field' => 'BusinessSession.description', 'position' => '["BusinessSession"]["description"]', 'alias' => 'BusinessSession.description'),
			array('field' => 'BusinessSessionsCost.name', 'position' => '["BusinessSessionsCost"]["name"]', 'alias' => 'BusinessSessionsCost.name'),
			array('field' => 'CostType.name', 'position' => '["CostType"]["name"]', 'alias' => 'CostType.name'),
			array('field' => 'BusinessSessionsCost.quantity', 'position' => '["BusinessSessionsCost"]["quantity"]', 'alias' => 'BusinessSessionsCost.quantity'),
			array('field' => 'BusinessSessionsCost.price', 'position' => '["BusinessSessionsCost"]["price"]', 'alias' => 'BusinessSessionsCost.price'),
			array('field' => $this->Contract->ContactPerson->full_name . ' AS cp_full_name', 'position' => '[0]["cp_full_name"]', 'alias' => 'ContactPerson.fullname'),
			array('field' => 'Contract.month', 'position' => '["Contract"]["month"]', 'alias' => 'Contract.month'),
			array('field' => 'Contract.year', 'position' => '["Contract"]["year"]', 'alias' => 'Contract.year'),
			array('field' => 'Contract.amount_vat', 'position' => '["Contract"]["amount_vat"]', 'alias' => 'Contract.amount_vat'),
			array('field' => 'BusinessSessionType.name', 'position' => '["BusinessSessionType"]["name"]', 'alias' => 'BusinessSessionType.name'),
			array('field' => 'BusinessSessionState.name', 'position' => '["BusinessSessionState"]["name"]', 'alias' => 'BusinessSessionState.name'),
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
			$conditions[] = $this->Purchaser->virtualFields['name'] . ' LIKE \'%%' . $data['Purchaser']['name'] . '%%\'';
		}
		if ( !empty($data['Address']['street']) ){
			$conditions[] = 'Address.street LIKE \'%%' . $data['Address']['street'] . '%%\'';
		}
		if ( !empty($data['Address']['city']) ){
			$conditions[] = 'Address.city LIKE \'%%' . $data['Address']['city'] . '%%\'';
		}
		if ( !empty($data['Purchaser']['icz']) ){
			$conditions[] = 'Purchaser.icz LIKE \'%%' . $data['Purchaser']['icz'] . '%%\'';
		}
		if ( !empty($data['Purchaser']['category']) ){
			$conditions[] = 'Purchaser.category LIKE \'%%' . $data['Purchaser']['category'] . '%%\'';
		}
		if ( !empty($data['BusinessSession']['date_from']) ){
			$date_from = explode('.', $data['BusinessSession']['date_from']);
			$date_from = $date_from[2] . '-' . $date_from[1] . '-' . $date_from[0];
			$conditions[] = 'DATE(BusinessSession.date) >= \'' . $date_from . '\'';
		}
		if ( !empty($data['BusinessSession']['date_to']) ){
			$date_to = explode('.', $data['BusinessSession']['date_to']);
			$date_to = $date_to[2] . '-' . $date_to[1] . '-' . $date_to[0];
			$conditions[] = 'DATE(BusinessSession.date) <= \'' . $date_to . '\'';
		}
		if (!empty($data['BusinessSession']['business_session_type_id'])) {
			$conditions[] = 'BusinessSession.business_session_type_id = ' . $data['BusinessSession']['business_session_type_id'];
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
			business_partners AS BusinessPartner ON (Purchaser.business_partner_id = BusinessPartner.id) LEFT JOIN
			addresses AS Address ON (Address.purchaser_id = Purchaser.id)
		WHERE ' . $conditions;

		return $query;
	}
	
	function paginate($conditions, $fields, $order, $limit = 20, $page = 1, $recursive, $extra) {
		$contain = $extra['contain'];
		$joins = $extra['joins'];

		$query = $this->basic_query($conditions);
		
		$business_session_ids = $this->query($query);
		$business_session_ids = Set::extract('/BusinessSession/id', $business_session_ids);
		
		$business_sessions = $this->find('all', array(
			'conditions' => array('BusinessSession.id' => $business_session_ids),
			'contain' => $contain,
			'order' => $order,
			'joins' => $joins,
			'fields' => $fields,
			'limit' => $limit,
			'group' => 'BusinessSession.id',
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
			
		// pole kde jsou data typu datetim
		$datetime_fields = array();
		
		// pole kde jsou data typu date
		$date_fields = array();
		
		// exportuju udaj o tom, ktera pole jsou soucasti vystupu
		$find['fields'] = Set::extract('/field', $export_fields);
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
				$escape_line_breaks = false;
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
/*					if ($position == '[0]["total_amount"]' && empty($result)) {
						$result = 0;
					}*/
					// odstranim nove radky
					$result = str_replace("\r\n", ' ', $result);
					// odstranim 
					$results[] = $result;
				}
			}
			$line = implode(';', $results);
			// ulozim radek
			fwrite($file, iconv('utf-8', 'windows-1250//TRANSLIT', $line . "\n"));
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
	
	// OJ je smazatelne, pokud nema zadne naklady, ani dohody
	function isDeletable($id) {
		$businessSession = $this->find('first', array(
			'conditions' => array('BusinessSession.id' => $id),
			'contain' => array(
				'BusinessSessionsCost' => array(
					'fields' => array('BusinessSessionsCost.id')
				),
				'Contract' => array(
					'fields' => array('Contract.id')
				),
			),
			'fields' => array('BusinessSession.id')
		));
		
		return (empty($businessSession['BusinessSessionsCost']) && empty($businessSession['Contract']));
	}
}
