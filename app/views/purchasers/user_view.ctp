<?php
if (isset($this->params['named']['tab'])) {
	$tab_pos = $this->params['named']['tab'];
?>
	<script>
		$(function() {
			$( "#tabs" ).tabs("select", "#tabs-<?php echo $tab_pos?>");
		});
	</script>
<?php } ?>

<h1><?php echo $purchaser['Purchaser']['full_title']?></h1>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Info</a></li>
		<li><a href="#tabs-6">Dokumenty</a></li>
		<li><a href="#tabs-7">Kont. osoby</a></li>
		<li><a href="#tabs-8">Obch. jednání</a></li>
		<li><a href="#tabs-9">Sklad</a></li>
		<li><a href="#tabs-10">Dod. listy</a></li>
		<li><a href="#tabs-11">Poukazy</a></li>
		<li><a href="#tabs-12">Pohyby</a></li>
<?php if ($acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Purchasers/user_wallet_correction')) { ?>
		<li><a href="#tabs-13">Korekce peněženky</a>
<?php } ?>
	</ul>
	
<?php /* TAB 1 ****************************************************************************************************************/ ?>
	<div id="tabs-1">
		<h2>Základní informace</h2>
		<table class="left_heading">
			<tr>
				<th>Email</th>
				<td><?php echo $purchaser['Purchaser']['email']?></td>
			</tr>
			<tr>
				<th>Telefon</th>
				<td><?php echo $purchaser['Purchaser']['phone']?></td>
			</tr>
			<tr>
				<th>IČZ</th>
				<td><?php echo $purchaser['Purchaser']['icz']?></td>
			</tr>
			<tr>
				<th>Poznámka</th>
				<td><?php echo $purchaser['Purchaser']['note']?></td>
			</tr>
			<tr>
				<th>Bonita</th>
				<td><?php echo (isset($bonity[$purchaser['Purchaser']['bonity']]) ? $bonity[$purchaser['Purchaser']['bonity']] : '')?></td>
			</tr>
			<tr>
				<th>Stav účtu</th>
				<td><?php echo $purchaser['Purchaser']['wallet']?>&nbsp;Kč</td>
			</tr>
			<tr>
				<th>Obchodní partner</th>
				<td><?php echo $this->Html->link($purchaser['BusinessPartner']['name'], array('controller' => 'business_partners', 'action' => 'view', $purchaser['BusinessPartner']['id']))?></td>
			</tr>
			<tr>
				<th>Uživatel</th>
				<td><?php echo $purchaser['Purchaser']['user_full_name'] ?></td>
			</tr>
		</table>
		
		<h2>Doručovací adresa</h2>
		<table class="left_heading">
			<tr>
				<th>Adresa</th>
				<td><?php echo $purchaser['Purchaser']['address_one_line']?></td>
			</tr>
		</table>
		<ul>
			<li><?php echo $html->link('Upravit odběratele', array('controller' => 'purchasers', 'action' => 'edit', $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])))?>
		</ul>
	</div>
<?php /* TAB 6 ****************************************************************************************************************/ ?>
	<div id="tabs-6">
		<h2>Dokumenty</h2>
		<button id="search_form_show_documents">vyhledávací formulář</button>
		<?php
			$hide = ' style="display:none"';
			if ( isset($this->data['DocumentForm2']) ){
				$hide = '';
			}
		?>
		<div id="search_form_documents"<?php echo $hide?>>
			<?php echo $form->create('Document', array('url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 6))); ?>
			<table class="left_heading">
				<tr>
					<th>Název</th>
					<td><?php echo $form->input('DocumentForm2.Document.title', array('label' => false))?></td>
					<th>Vloženo</th>
					<td><?php echo $form->input('DocumentForm2.Document.created', array('label' => false, 'type' => 'text'))?></td>
				</tr>
				<tr>
					<td colspan="4"><?php echo $html->link('reset filtru', array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'reset' => 'documents'))?></td>
				</tr>
			</table>
			<?php
				echo $form->hidden('DocumentForm2.Document.search_form', array('value' => 1));
				echo $form->submit('Vyhledávat');
				echo $form->end();
			?>
		</div>
		
		<script>
			$("#search_form_show_documents").click(function () {
				if ($('#search_form_documents').css('display') == "none"){
					$("#search_form_documents").show("slow");
				} else {
					$("#search_form_documents").hide("slow");
				}
			});
		
			$(function() {
				var dates = $( "#DocumentForm2DocumentCreated" ).datepicker({
					defaultDate: "+1w",
					changeMonth: false,
					numberOfMonths: 1,
					onSelect: function( selectedDate ) {
						var option = this.id == "DocumentForm2DocumentCreated" ? "minDate" : "maxDate",
							instance = $( this ).data( "datepicker" ),
							date = $.datepicker.parseDate(
								instance.settings.dateFormat ||
								$.datepicker._defaults.dateFormat,
								selectedDate, instance.settings );
						dates.not( this ).datepicker( "option", option, date );
					}
				});
			});
			$( "#datepicker" ).datepicker( $.datepicker.regional[ "cs" ] );
		</script>
		
		<?php if (empty($documents)) { ?>
		<p><em>K tomuto odběrateli nejsou přiděleny žádné dokumenty.</em></p>
		<?php } else { ?>
		<table class="top_heading">
			<tr>
				<th>ID</th>
				<th>Vloženo</th>
				<th>Název</th>
				<th>&nbsp;</th>
			</tr>
		<?php
			$odd = '';
			foreach ($documents as $document) {
				$odd = ( $odd == ' class="odd"' ? '' : ' class="odd"' );
		?>
			<tr<?php echo $odd?>>
				<td><?php echo $document['Document']['id']?></td>
				<td><?php echo $document['Document']['created']?></td>
				<td><?php echo $html->link($document['Document']['title'], '/files/documents/' . $document['Document']['name'], array('target' => 'blank'))?></td>
				<td class="actions">
					<?php echo $html->link('Přejmenovat', array('controller' => 'documents', 'action' => 'rename', $document['Document']['id']))?>
					<?php echo $html->link('Smazat', array('controller' => 'documents', 'action' => 'delete', $document['Document']['id']), null, 'Opravdu chcete dokument ' . $document['Document']['title'] . ' smazat?')?>
				</td>
			</tr>
		<?php } ?>
		</table>
		<?php }	?>
		
		<h3>Nahrát dokument z disku</h3>
<?
		echo $form->Create('Document', array('url' => array('controller' => 'documents', 'action' => 'add'), 'type' => 'file')); ?>
		<fieldset>
			<legend>Nový dokument z disku</legend>
			<table class="left_heading" cellpadding="5" cellspacing="3">
				<tr>
					<td><input type="file" name="data[Document][document0]" /></td>
					<td><?php echo $form->input('Document.document0.title', array('label' => 'Titulek:', 'size' => 40)); ?></td>
					<td><?php echo $form->submit('Nahrát dokument');?></td>
				</tr>
			</table>
		</fieldset>
<?
		echo $form->hidden('Document.document_fields', array('value' => 1));
		echo $form->hidden('Document.purchaser_id', array('value' => $purchaser['Purchaser']['id']));
		echo $form->end();
?>

		<h3>Nahrát dokument z webu</h3>
		<?php echo $form->create('Document', array('url' => array('controller' => 'documents', 'action' => 'add_from_web')))?>
		<fieldset>
			<legend>Nový dokument z webu</legend>
			<table class="leftHeading" cellpadding="5" cellspacing="5">
<? 				$this->data['BusinessPartner']['web_document_fields'] = 1; ?>
				<tr>
					<th>URL</th>
					<td><?php echo $form->input('Document.data.0.url', array('label' => false, 'size' => 100))?></td>
				</tr>
				<tr>
					<th>Název souboru</th>
					<td><?php echo $form->input('Document.data.0.name', array('label' => false, 'size' => 50))?></td>
				</tr>
				<tr>
					<th>Titulek dokumentu</th>
					<td><?php echo $form->input('Document.data.0.title', array('label' => false, 'size' => 50))?></td>
				</tr>
			</table>
		<?php
			echo $form->hidden('Document.purchaser_id', array('value' => $purchaser['Purchaser']['id']));
		?>
		</fieldset>
<?php 	
	echo $form->submit('Nahrát dokument z webu');
	echo $form->end();
?>
	</div>

<?php /* TAB 7 ****************************************************************************************************************/ ?>
	<div id="tabs-7">
		<h2>Kontaktní osoby</h2>
<?php	echo $this->element('search_forms/contact_people', array('url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 7)));

		echo $form->create('CSV', array('url' => array('controller' => 'contact_people', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($contact_people_find)));
		echo $form->hidden('fields', array('value' => serialize($contact_people_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
?>
		<ul>
			<li><?php echo $html->link('Zadat kontaktní osobu', array('controller' => 'contact_people', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => $contact_people_back_link))?>
		</ul>
		<?php if (empty($contact_people)) { ?>
		<p><em>Odběratel nemá zadané žádné kontaktní osoby.</em></p>
		<?php } else {
		$paginator->options(array(
			'url' => array('tab' => 7, 0 => $purchaser['Purchaser']['id'])
		));
		$paginator->params['paging'] = $contact_people_paging;
		$paginator->__defaultModel = 'ContactPerson';
		echo $this->element('indexes/contact_people', array('back_link' => $contact_people_back_link));
		?>
		<?php } // end if ?>
	</div>
	
<?php /* TAB 8 ****************************************************************************************************************/ ?>
	<div id="tabs-8">
		<h2>Obchodní jednání</h2>
		<?php
		echo $this->element('search_forms/business_sessions', array('url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 8)));
		 
		echo $form->create('CSV', array('url' => array('controller' => 'business_sessions', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($business_sessions_find)));
		echo $form->hidden('fields', array('value' => serialize($business_sessions_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		?>
		<ul>
			<li><?php echo $html->link('Zadat obchodní jednání', array('controller' => 'business_sessions', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => $business_sessions_back_link))?></li>
		</ul>
		
<?php if (empty($business_sessions)) { ?>
		<p><em>K odběrateli se nevztahují žádná jednání.</em></p>
<?php } else { 
		$paginator->options(array(
			'url' => array('tab' => 8, 0 => $purchaser['Purchaser']['id'])
		));
		$paginator->params['paging'] = $business_sessions_paging;
		$paginator->__defaultModel = 'BusinessSession';
		
		echo $this->element('indexes/business_sessions', array('back_link' => $business_sessions_back_link));
	} // end if ?>
	</div>
<?php /* TAB 9 ****************************************************************************************************************/ ?>
	<div id="tabs-9">
		<h2>Sklad</h2>

<?php
		echo $this->element('search_forms/stores', array('url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 9)));
				 
		echo $form->create('CSV', array('url' => array('controller' => 'store_items', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($store_items_find)));
		echo $form->hidden('fields', array('value' => serialize($store_items_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		
		echo $form->create('PDF', array('url' => array('controller' => 'store_items', 'action' => 'pdf_export')));
		echo $form->hidden('purchaser_id', array('value' => $purchaser['Purchaser']['id']));
		echo $form->submit('PDF');
		echo $form->end();

		if (empty($store_items)) { ?>
		<p><em>Sklad oodběratele je prázdný.</em></p>
		<?php } else { 
			$paginator->options(array(
				'url' => array('tab' => 9, 0 => $purchaser['Purchaser']['id'])
			));
	
			$paginator->params['paging'] = $store_items_paging;
			$paginator->__defaultModel = 'StoreItem';
			
			echo $this->element('indexes/store_items', array('stores' => $store_items));
		} // end if ?>
	</div>
	
<?php /* TAB 10 ****************************************************************************************************************/ ?>
	<div id="tabs-10">
		<h2>Dodací listy</h2>
<?php
		echo $this->element('search_forms/delivery_notes', array('users' => $delivery_notes_users, 'url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 10)));
	
		echo $form->create('CSV', array('url' => array('controller' => 'delivery_notes', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($delivery_notes_find)));
		echo $form->hidden('fields', array('value' => serialize($delivery_notes_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		?>
		
		<ul>
			<li><?php echo $this->Html->link('Přidat dodací list', array('controller' => 'delivery_notes', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])))?></li>
		</ul>
		
		<?php if (empty($delivery_notes)) { ?>
		<p><em>V systému nejsou žádné dodací listy.</em></p>
		<?php } else { 
			$paginator->options(array(
				'url' => array('tab' => 10, 0 => $purchaser['Purchaser']['id'])
			));
			
			$paginator->params['paging'] = $delivery_notes_paging;
			$paginator->__defaultModel = 'DeliveryNote'; 
			echo $this->element('indexes/transactions', array('model' => 'DeliveryNote', 'transactions' => $delivery_notes));
		} ?>
		
	</div>
	
<?php /* TAB 11 ****************************************************************************************************************/ ?>
	<div id="tabs-11">
		<h2>Poukazy</h2>

<?php
		echo $this->element('search_forms/sales', array('users' => $sales_users, 'url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 11)));
		 
		echo $form->create('CSV', array('url' => array('controller' => 'sales', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($sales_find)));
		echo $form->hidden('fields', array('value' => serialize($sales_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		?>
		
		<ul>
			<li><?php echo $this->Html->link('Přidat poukaz', array('controller' => 'sales', 'action' => 'add', 'purchaser_id' => $purchaser['Purchaser']['id'], 'back_link' => base64_encode($_SERVER['REQUEST_URI'])))?></li>
		</ul>
		
		<?php if (empty($sales)) { ?>
		<p><em>V systému nejsou žádné poukazy.</em></p>
		<?php } else { 
			$paginator->options(array(
				'url' => array('tab' => 11, 0 => $purchaser['Purchaser']['id'])
			));
			
			$paginator->params['paging'] = $sales_paging;
			$paginator->__defaultModel = 'Sale';

			echo $this->element('indexes/transactions', array('model' => 'Sale', 'transactions' => $sales));
		} ?>
	</div>
	
<?php /* TAB 12 ****************************************************************************************************************/ ?>
	<div id="tabs-12">
		<h2>Pohyby</h2>
<?php
		echo $this->element('search_forms/transactions', array('users' => $transactions_users, 'url' => array('controller' => 'purchasers', 'action' => 'view', $purchaser['Purchaser']['id'], 'tab' => 12)));

		echo $form->create('CSV', array('url' => array('controller' => 'transactions', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($transactions_find)));
		echo $form->hidden('fields', array('value' => serialize($transactions_export_fields)));
		echo $form->submit('CSV');
		echo $form->end();

		if (empty($transactions)) { ?>
		<p><em>V systému nejsou žádné pohyby.</em></p>
		<?php } else { 
			$paginator->options(array(
				'url' => array('tab' => 12, 0 => $purchaser['Purchaser']['id'])
			));
			
			$paginator->params['paging'] = $transactions_paging;
			$paginator->__defaultModel = 'Transaction'; 
			
			echo $this->element('indexes/transactions', array('model' => 'Transaction', 'transactions' => $transactions));
		}
		?>

	</div>
	
<?php if ($acl->check(array('model' => 'User', 'foreign_key' => $session->read('Auth.User.id')), 'controllers/Purchasers/user_wallet_correction')) { ?>
<?php /* TAB 13 ****************************************************************************************************************/ ?>
	<div id="tabs-13">
		<h2>Korekce peněženky</h2>
<?php 
		echo $form->create('CSV', array('url' => array('controller' => 'wallet_transactions', 'action' => 'xls_export')));
		echo $form->hidden('data', array('value' => serialize($wallet_transactions_find)));
		echo $form->hidden('fields', array('value' => serialize($wallet_transactions_export_fields)));
		echo $form->hidden('virtual_fields', array('value' => serialize($wallet_transactions_virtual_fields)));
		echo $form->submit('CSV');
		echo $form->end();
		
		if (empty($wallet_transactions)) {
?>
		<p><em>V systému nejsou žádné korekce peněženky.</em></p>
		<?php } else {
			$paginator->options(array(
				'url' => array('tab' => 13, 0 => $purchaser['Purchaser']['id'])
			));
			
			$paginator->params['paging'] = $wallet_transactions_paging;
			$paginator->__defaultModel = 'WalletTransaction';
?>
		
		<table class="top_heading">
			<tr>
				<th><?php echo $paginator->sort('Datum', 'WalletTransaction.created')?></th>
				<th><?php echo $paginator->sort('Hodnota', 'WalletTransaction.amount')?></th>
				<th><?php echo $paginator->sort('Peněženka před', 'WalletTransaction.wallet_before')?></th>
				<th><?php echo $paginator->sort('Peněženka po', 'WalletTransaction.wallet_after')?></th>
				<th><?php echo $paginator->sort('Zadal', 'WalletTransaction.user_name')?></th>
			</tr>
			<?php foreach ($wallet_transactions as $wallet_transaction) { ?>
			<tr>
				<td><?php echo $wallet_transaction['WalletTransaction']['created']?></td>
				<td align="right"><?php echo $wallet_transaction['WalletTransaction']['amount']?></td>
				<td align="right"><?php echo $wallet_transaction['WalletTransaction']['wallet_before']?></td>
				<td align="right"><?php echo $wallet_transaction['WalletTransaction']['wallet_after']?></td>
				<td><?php echo $wallet_transaction['WalletTransaction']['user_name']?></td>
			</tr>
			<?php } ?>
		</table>
<?php } ?>
	</div>
<?php } ?>
</div>