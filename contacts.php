<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2019
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Giovanni Maruzzelli <gmaruzz@opentelecom.it>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('contact_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get posted data
	if (is_array($_POST['contacts'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$contacts = $_POST['contacts'];
	}

//process the http post data by action
	if ($action != '' && is_array($contacts) && @sizeof($contacts) != 0) {
		switch ($action) {
			case 'delete':
				if (permission_exists('contact_delete')) {
					$obj = new contacts;
					$obj->delete($contacts);
				}
				break;
		}

		header('Location: contacts.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//retrieve current user's assigned groups (uuids)
	foreach ($_SESSION['groups'] as $group_data) {
		$user_group_uuids[] = $group_data['group_uuid'];
	}

//add user's uuid to group uuid list to include private (non-shared) contacts
	$user_group_uuids[] = $_SESSION["user_uuid"];

//get contact settings - sync sources
	$sql = "select ";
	$sql .= "contact_uuid, ";
	$sql .= "contact_setting_value ";
	$sql .= "from ";
	$sql .= "v_contact_settings ";
	$sql .= "where ";
	$sql .= "domain_uuid = :domain_uuid ";
	$sql .= "and contact_setting_category = 'sync' ";
	$sql .= "and contact_setting_subcategory = 'source' ";
	$sql .= "and contact_setting_name = 'array' ";
	$sql .= "and contact_setting_value <> '' ";
	$sql .= "and contact_setting_value is not null ";
	if (!(if_group("superadmin") || if_group("admin"))) {
		$sql .= "and ( "; //only contacts assigned to current user's group(s) and those not assigned to any group
		$sql .= "	contact_uuid in ( ";
		$sql .= "		select contact_uuid from v_contact_groups ";
		$sql .= "		where ";
		if (is_array($user_group_uuids) && @sizeof($user_group_uuids) != 0) {
			foreach ($user_group_uuids as $index => $user_group_uuid) {
				if (is_uuid($user_group_uuid)) {
					$sql_where_or[] = "group_uuid = :group_uuid_".$index;
					$parameters['group_uuid_'.$index] = $user_group_uuid;
				}
			}
			if (is_array($sql_where_or) && @sizeof($sql_where_or) != 0) {
				$sql .= " ( ".implode(' or ', $sql_where_or)." ) ";
			}
			unset($sql_where_or, $index, $user_group_uuid);
		}
		$sql .= "		and domain_uuid = :domain_uuid ";
		$sql .= "	) ";
		$sql .= "	or ";
		$sql .= "	contact_uuid not in ( ";
		$sql .= "		select contact_uuid from v_contact_groups ";
		$sql .= "		where group_uuid = :group_uuid ";
		$sql .= "		and domain_uuid = :domain_uuid ";
		$sql .= "	) ";
		$sql .= ") ";
	}
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$parameters['group_uuid'] = $_SESSION['group_uuid'];
	$database = new database;
	$result = $database->select($sql, $parameters, 'all');
	if (is_array($result) && @sizeof($result) != 0) {
		foreach($result as $row) {
			$contact_sync_sources[$row['contact_uuid']][] = $row['contact_setting_value'];
		}
	}
	unset($sql, $parameters, $result);

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];
	$user_extension = $_GET["user_extension"];

//add the search term
	$search = strtolower($_GET["search"]);
	if (strlen($search) > 0) {
		if (is_numeric($search)) {
			$sql_search .= "and contact_uuid in ( ";
			$sql_search .= "	select contact_uuid from v_contact_phones ";
			$sql_search .= "	where phone_number like :search ";
			$sql_search .= ") ";
		}
		else {
			$sql_search .= "and contact_uuid in ( ";
			$sql_search .= "	select contact_uuid from v_contacts ";
			$sql_search .= "	where domain_uuid = :domain_uuid ";
			$sql_search .= "	and ( ";
			$sql_search .= "		lower(contact_organization) like :search or ";
			$sql_search .= "		lower(contact_name_given) like :search or ";
			$sql_search .= "		lower(contact_name_family) like :search or ";
			$sql_search .= "		lower(contact_nickname) like :search or ";
			$sql_search .= "		lower(contact_title) like :search or ";
			$sql_search .= "		lower(contact_category) like :search or ";
			$sql_search .= "		lower(contact_role) like :search or ";
			$sql_search .= "		lower(contact_url) like :search or ";
			$sql_search .= "		lower(contact_time_zone) like :search or ";
			$sql_search .= "		lower(contact_note) like :search or ";
			$sql_search .= "		lower(contact_type) like :search ";
			$sql_search .= "	) ";
			$sql_search .= ") ";
		}
		$parameters['search'] = '%'.$search.'%';
	}

//build query for paging and list
	$sql = "select count(*) ";
	$sql .= "from v_contacts as c ";
	$sql .= "where domain_uuid = :domain_uuid ";
	if (!(if_group("superadmin") || if_group("admin"))) {
		$sql .= "and ( "; //only contacts assigned to current user's group(s) and those not assigned to any group
		$sql .= "	contact_uuid in ( ";
		$sql .= "		select contact_uuid from v_contact_groups ";
		$sql .= "		where ";
		if (is_array($user_group_uuids) && @sizeof($user_group_uuids) != 0) {
			foreach ($user_group_uuids as $index => $user_group_uuid) {
				if (is_uuid($user_group_uuid)) {
					$sql_where_or[] = "group_uuid = :group_uuid_".$index;
					$parameters['group_uuid_'.$index] = $user_group_uuid;
				}
			}
			if (is_array($sql_where_or) && @sizeof($sql_where_or) != 0) {
				$sql .= " ( ".implode(' or ', $sql_where_or)." ) ";
			}
			unset($sql_where_or, $index, $user_group_uuid);
		}
		$sql .= "		and domain_uuid = :domain_uuid ";
		$sql .= "	) ";
		$sql .= "	or contact_uuid in ( ";
		$sql .= "		select contact_uuid from v_contact_users ";
		$sql .= "		where user_uuid = :user_uuid ";
		$sql .= "		and domain_uuid = :domain_uuid ";
		$sql .= "";
		$sql .= "	) ";
		$sql .= ") ";
		$parameters['user_uuid'] = $_SESSION['user_uuid'];
	}
	$sql .= $sql_search;
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "&search=".$search;
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page); //bottom
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true); //top
	$offset = $rows_per_page * $page;

//get the list
	$sql = str_replace('count(*)', '*, (select a.contact_attachment_uuid from v_contact_attachments as a where a.contact_uuid = c.contact_uuid and a.attachment_primary = 1) as contact_attachment_uuid', $sql);
	if ($order_by != '') {
		$sql .= order_by($order_by, $order);
		$sql .= ", contact_organization asc ";
	}
	else {
		$contact_default_sort_column = $_SESSION['contacts']['default_sort_column']['text'] != '' ? $_SESSION['contacts']['default_sort_column']['text'] : "last_mod_date";
		$contact_default_sort_order = $_SESSION['contacts']['default_sort_order']['text'] != '' ? $_SESSION['contacts']['default_sort_order']['text'] : "desc";

		$sql .= order_by($contact_default_sort_column, $contact_default_sort_order);
		if ($db_type == "pgsql") {
			$sql .= " nulls last ";
		}
	}
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$contacts = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//get the contact list  
	$sql = "select * from v_contact_phones ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$sql .= "and contact_uuid = :contact_uuid ";
	$sql .= "order by phone_primary desc, phone_label asc ";
	$parameters['domain_uuid'] = $domain_uuid;
	$parameters['contact_uuid'] = $contact_uuid;
	$database = new database;
	$contact_phones = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);


//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//includes and title
	$document['title'] = $text['title-contacts'];

//show the content
    echo "<head> \n";
    echo "  <meta charset=\"utf-8\"> \n";
    echo "	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> \n";
    echo "	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"> \n";
    echo "	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"> \n";
    echo "	<link href=\"css/bootstrap.min.css\" rel=\"stylesheet\"> \n";
    echo "	<link href=\"css/high2.css\" rel=\"stylesheet\"> \n";
    echo "</head> \n";
    echo "\n";
    echo "<body> \n";

//contact attachment layer
	echo "<style>\n";
	echo "	#contact_attachment_layer {\n";
	echo "		z-index: 999999;\n";
	echo "		position: absolute;\n";
	echo "		left: 0px;\n";
	echo "		top: 0px;\n";
	echo "		right: 0px;\n";
	echo "		bottom: 0px;\n";
	echo "		text-align: center;\n";
	echo "		vertical-align: middle;\n";
	echo "	}\n";
	echo "</style>\n";
	echo "<style>\n";
	echo "  body {\n";
	echo "  background-color: #333 !important;\n";
    echo "  font-color: #333 !important;}\n";
	echo "</style>\n";

	echo "<div id='contact_attachment_layer' style='display: none;'></div>\n";

//javascript function: send_cmd
	echo "<script type=\"text/javascript\">\n";
	echo "function send_cmd(url) {\n";
	echo "	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari\n";
	echo "		xmlhttp=new XMLHttpRequest();\n";
	echo "	}\n";
	echo "	else {// code for IE6, IE5\n";
	echo "		xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n";
	echo "	}\n";
	echo "	xmlhttp.open(\"GET\",url,true);\n";
	echo "	xmlhttp.send(null);\n";
	echo "}\n";
	echo "</script>\n";



	//echo "<div style=\"background-color:white;\">";
    echo "<div style=\"background-color: #333;\">\n";
   

//show the content
	echo "<div class='action_bar' style=\"padding: 10px;\" id='action_bar'>\n";
	echo "	<div class='heading'><b>Contacts".$text['header-contacts']." (".$num_rows.")</b></div>\n";
	echo "	<div style=\"color: black;\" class='actions'>\n";
	echo 		"<form id='form_search' class='inline' method='get';>\n";
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>"CERCA",'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>"RESET",'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'contacts.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['description-contacts']."\n";
//	echo "<br /><br />\n";
//	echo "<br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header' style=\"font-size: 16px;\">\n";
	echo th_order_by('contact_organization', "Company", $order_by, $order);
	echo th_order_by('contact_name_given', "Name   ", $order_by, $order);
	echo th_order_by('contact_name_family', "Surname", $order_by, $order);
	echo th_order_by('contact_name_family', "Numbers", $order_by, $order);
	echo "</tr>\n";

	if (is_array($contacts) && @sizeof($contacts) != 0) {
		$x = 0;
		foreach($contacts as $row) {
			echo "<tr class='list-row' style=\"font-size: 14px;\" href='".$list_row_url."'>\n";
			echo "	<td class='overflow'><a href='".$list_row_url."'>".escape($row['contact_organization'])."</a>&nbsp;&nbsp;&nbsp;</td>\n";
			echo "	<td class='overflow'><a href='".$list_row_url."'>".escape($row['contact_name_given'])."</a>&nbsp;&nbsp;&nbsp;</td>\n";
			echo "	<td class='no-wrap'><a href='".$list_row_url."'>".escape($row['contact_name_family'])."</a>&nbsp;&nbsp;&nbsp;</td>\n";
			/***************************************************************************/
			//get the contact list  
			$sql9 = "select * from v_contact_phones ";
			$sql9 .= "where domain_uuid = :domain_uuid ";
			$sql9 .= "and contact_uuid = :contact_uuid ";
			$sql9 .= "order by phone_primary desc, phone_label asc ";
			$parameters9['domain_uuid'] = $_SESSION['domain_uuid'];
			$parameters9['contact_uuid'] = $row['contact_uuid'];
			$database9 = new database;
			$contact_phones9 = $database9->select($sql9, $parameters9, 'all');
			unset($sql9, $parameters9);
			echo "	<td class='tr_link_void'>" ;
			if (is_array($contact_phones9) && @sizeof($contact_phones9) != 0) {
				foreach($contact_phones9 as $row9) {
					echo "		<a href=\"javascript:void(0)\" onclick=\"window.parent.parent.scrollTo(0,0);send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".escape(urlencode($row9['phone_number']))."&src_cid_number=".escape(urlencode($row9['phone_number']))."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode(escape($_SESSION['user']['extension'][0]['outbound_caller_id_number']))."&src=".urlencode(escape($user_extension))."&dest=".escape(urlencode($row9['phone_number']))."&rec=false&ringback=us-ring&auto_answer=true');\">\n";
					echo "		".escape(format_phone($row9['phone_number']))."</a>&nbsp;\n";
				}
			}
			echo "&nbsp;</td>\n";
			/***************************************************************************/
			echo "</tr>\n";
			$x++;
		}
		unset($contacts);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";

	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>\n";

//javascript
	echo "<script>\n";
	echo "	function display_attachment(id) {\n";
	echo "		$('#contact_attachment_layer').load('contact_attachment.php?id=' + id + '&action=display', function(){\n";
	echo "			$('#contact_attachment_layer').fadeIn(200);\n";
	echo "		});\n";
	echo "	}\n";
	echo "</script>\n";

	echo "</div>\n";
    echo "</body>\n";
?>
