<?php
/*
   FusionPBX
   Version: MPL 1.1

   The contents of this file are subject to Mozilla Public License Version
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
   Portions created by the Initial Developer are Copyright (C) 2008-2012
   the Initial Developer. All Rights Reserved.

   The Module Initial Developer of this module is
   Giovanni Maruzzelli <gmaruzz@opentelecom.it>
   Portions created by the Module Initial Developer are Copyright (C) 2020
   the Module Initial Developer. All Rights Reserved.

   SaraPhone gets its name from Giovanni's wife, Sara.

   Author(s) this module:
   Giovanni Maruzzelli <gmaruzz@opentelecom.it>
   Danilo Volpinari
   Luca Mularoni
 */

//includes
require_once "root.php";
require_once "resources/require.php";
require_once "resources/header.php";

//check permissions
require_once "resources/check_auth.php";
if (permission_exists('saraphone_call')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();

$sql3 = "SELECT distinct d.device_mac_address, extension,d.device_template,display_name,effective_caller_id_name,outbound_caller_id_number FROM v_extension_users, v_extensions, v_users,v_device_lines AS l, v_devices AS d WHERE ((l.user_id = extension) AND (v_users.user_uuid = v_extension_users.user_uuid) AND (v_extensions.extension_uuid = v_extension_users.extension_uuid)  AND (v_extensions.domain_uuid = '" . $_SESSION["domain_uuid"] . "') AND (l.user_id=extension) AND (l.device_uuid = d.device_uuid) AND (v_users.user_uuid = '" . $_SESSION['user_uuid'] . "') AND (d.domain_uuid = '" . $_SESSION["domain_uuid"] . "')) ORDER BY extension, d.device_mac_address asc";
$database3 = new database;
$rows3 = $database3->select($sql3, NULL, 'all');

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

//show the content
echo "<head> \n";
echo "    <meta charset=\"utf-8\"> \n";
echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> \n";
echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"> \n";
echo "    <meta http-equiv=\"expires\" content=\"Sun, 01 Jan 2014 00:00:00 GMT\"/> \n";
echo "    <meta http-equiv=\"pragma\" content=\"no-cache\" /> \n";
echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"> \n";
echo "    <meta name=\"description\" content=\"".$text['head_description']."\"> \n";
echo "    <meta name=\"author\" content=\"Giovanni Maruzzelli\"> \n";
echo "    <link rel=\"icon\" href=\"favicon.ico\"> \n";
echo "    <title>SaraPhone WebRTC</title> \n";
echo "    <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"css/style2.css\" rel=\"stylesheet\"> \n";
echo "</head> \n";
echo "\n";
echo "<body> \n";

$wanted_device = $_GET['wanted_device'] ;

if( strlen($rows3[0]['device_mac_address']) ){

	if( ! strlen($rows3[1]['device_mac_address']) ){
		//user has one and only one device, go for it directly
		$wanted_device = $rows3[0]['device_mac_address'] ;
	}
}

if(! strlen($wanted_device)) {
	echo "<div class=\"container\"> \n";
	echo "    <div align=\"center\" class=\"form-signin\"> \n";
	echo "    <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"margin-bottom: 10px;\" > \n";
	echo "        <div class=\"col-8 col-sm-8 col-md-8 col-lg-8 col-xl-8\" > \n";
	// choose which phone
	echo "            <div align=\"center\" class=\"form-signin\"> \n";
	echo "                <div align=\"center\" id=\"signin\" class=\"form-signin-content\"> \n";
	echo "                    <div class=\"row\"> \n";
	echo "                        <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                            <div id=\"webphone_body\" > \n";
	echo "                                <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"color: #808080\"> \n";
	echo "                                    <br/> \n";
	echo "                                    <i>SaraPhone WebRTC</i> \n";
	echo "                                </div> \n";
	echo "                                <br/>  \n";
	echo "                                <br/>  \n";
	echo "                                <div align=\"center\">\n";
	echo "                                    <br>&nbsp;</br>\n";
	$conta1=0;
	echo "                                    <select class=\"form-control\" id=\"wanted_device\" style=\"width: 420px;text-align:center;\"> \n";
	while( strlen($rows3[$conta1]['device_mac_address']) ){
		echo "<option value=\"" . $rows3[$conta1]['device_mac_address'] . "\"> EXT=" . $rows3[$conta1]['extension'] .  " LABEL=" . $rows3[$conta1]['display_name'] .  " INT_NAME=" . $rows3[$conta1]['effective_caller_id_name'] .  " OUT_NUM=" . $rows3[$conta1]['outbound_caller_id_number'] . " MODEL=" . $rows3[$conta1]['device_template'] .  " MAC=" . $rows3[$conta1]['device_mac_address'] .     "   </option> \n";
		$conta1++;
	}
	echo "                                    </select> \n";
	echo "                                    <br/> \n";
	echo "                                    <button style=\"width: 170px !important\" class=\"btn btn-md btn-primary btn-success\" data-inline=\"true\" id=\"choose_device\" onclick=\"window.location='/app/saraphone/saraphone.php?clicklogin=yes&wanted_device=' + document.getElementById('wanted_device').value  + ''\">".$text['choose_phone']."</button>\n" ;
	echo "                                    <br>&nbsp;</br>\n";
	echo "                                    <br/> \n";
	echo "                                    <br/> \n";
	//echo "                                    <button style=\"width: 150px !important\" class=\"btn btn-md btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel\" onclick=\"window.location='/core/user_settings/user_dashboard.php';\">".$text['back_to_dashboard']."</button> \n";
	echo "                                    <button style=\"width: 150px !important\" class=\"btn btn-md btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel1\" onclick=\"window.location='/core/user_settings/user_dashboard.php';\">".$text['back_to_dashboard']."</button> \n";
	//	echo "                                    <br/> \n";
	echo "                                </div>\n";
	echo "                            </div>\n";
	echo "                        </div>\n";
	echo "                    </div>\n";
	echo "                </div>\n";
	echo "            </div>\n";
	echo "        </div>\n";
	echo "    </div>\n";
	echo "</div>\n";
} else {
// keypad sound
echo "<script> \n";
echo "var audio0 = new Audio(\"wav/0.wav\"); \n";
echo "var audio1 = new Audio(\"wav/1.wav\"); \n";
echo "var audio2 = new Audio(\"wav/2.wav\"); \n";
echo "var audio3 = new Audio(\"wav/3.wav\"); \n";
echo "var audio4 = new Audio(\"wav/4.wav\"); \n";
echo "var audio5 = new Audio(\"wav/5.wav\"); \n";
echo "var audio6 = new Audio(\"wav/6.wav\"); \n";
echo "var audio7 = new Audio(\"wav/7.wav\"); \n";
echo "var audio8 = new Audio(\"wav/8.wav\"); \n";
echo "var audio9 = new Audio(\"wav/9.wav\"); \n";
echo "var audio_star = new Audio(\"wav/star.wav\"); \n";
echo "var audio_hash = new Audio(\"wav/hash.wav\"); \n";
echo "var audio_silence = new Audio(\"wav/silence.wav\"); \n";
echo "</script> \n";


	$sql5 = "SELECT d.device_mac_address, extension,d.device_template,display_name,v_extensions.password,effective_caller_id_name,outbound_caller_id_number,register_expires, sip_transport, sip_port, server_address, outbound_proxy_primary FROM v_extension_users, v_extensions, v_users,v_device_lines AS l, v_devices AS d WHERE ((l.user_id = extension) AND (v_users.user_uuid = v_extension_users.user_uuid) AND (v_extensions.extension_uuid = v_extension_users.extension_uuid)  AND (v_extensions.domain_uuid = '" . $_SESSION["domain_uuid"] . "') AND (l.user_id=extension) AND (l.device_uuid = d.device_uuid) AND (v_users.user_uuid = '" . $_SESSION['user_uuid'] . "') AND (d.device_mac_address = '" . $wanted_device . "') ) ORDER BY extension, d.device_mac_address asc LIMIT 5";
	$database5 = new database;
	$rows5 = $database5->select($sql5, NULL, 'all');

	$user_extension = $rows5[0]['extension'];
	$user_password = $rows5[0]['password'];
	$effective_caller_id_name = $rows5[0]['effective_caller_id_name'];
	$sql4 = "SELECT d.device_mac_address, extension,d.device_template,display_name,v_extensions.password,effective_caller_id_name,outbound_caller_id_number,k.device_key_label, k.device_key_value, k.device_key_id, register_expires, sip_transport, sip_port, server_address, outbound_proxy_primary FROM v_extension_users, v_extensions, v_users,v_device_lines AS l, v_devices AS d, v_device_keys AS k WHERE ((l.user_id = extension) AND (v_users.user_uuid = v_extension_users.user_uuid) AND (v_extensions.extension_uuid = v_extension_users.extension_uuid)  AND (v_extensions.domain_uuid = '" . $_SESSION["domain_uuid"] . "') AND (l.user_id=extension) AND (l.device_uuid = d.device_uuid) AND (k.device_uuid = d.device_uuid) AND (v_users.user_uuid = '" . $_SESSION['user_uuid'] . "') AND (d.device_mac_address = '" . $wanted_device . "') ) ORDER BY extension, d.device_mac_address, k.device_key_id asc LIMIT 60";
	$database4 = new database;
	$rows = $database4->select($sql4, NULL, 'all');

	echo "<div class=\"container\"> \n";
	echo "    <div style=\"display: block; background-color: black;\" id=\"hideAll\"> \n";
	echo "	      <h2>".$text['wait_please']."</h2> \n";
	echo "    </div> \n";
	echo "    <div align=\"center\"> \n";
	echo "    \n";
	echo "        <div align=\"center\" class=\"form-signin\"> \n";
	echo "            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"margin-bottom: 10px;\" > \n";
	echo "                <div class=\"col-8 col-sm-8 col-md-8 col-lg-8 col-xl-8\" > \n";
	// login	
	echo "                    <div align=\"center\" class=\"form-signin\"> \n";
	echo "                        <div align=\"center\" id=\"signin\" class=\"form-signin-content\"> \n";
	echo "                            <div class=\"row\"> \n";
	echo "                                <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                                    <div id=\"webphone_body\" > \n";
	echo "                                        <br/>  \n";
	echo "                                        <br/>  \n";
	echo "                                        <input id=\"login\" style=\"max-width: 380px;\" class=\"form-control input-md\" placeholder=\"".$text['your_account_login']."\" value=\"" . $user_extension . "\" required autofocus> \n";
	echo "                                        <br/>  \n";
	echo "                                        <input id=\"passwd\" style=\"max-width: 380px;\" type=\"password\" class=\"form-control input-md\" placeholder=\"".$text['your_account_password']."\" value=\"" . $user_password . "\" required> \n";
	echo "                                        <br/>  \n";
	echo "                                        <input id=\"yourname\" style=\"max-width: 380px;\" type=\"text\" class=\"form-control input-md\" placeholder=\"".$text['your_display_name']."\" value=\"" . $effective_caller_id_name . "\"required> \n";
	echo "                                        <br/>  \n";
	echo "                                        <h3>This page is not supposed to stay here more than half a second. SOMETHING IS WRONG. YOU ARE PROBABLY NOT CONNECTED TO WSS SERVER. NETWORK PROBLEM or MISCONFIGURATION (wss_proxy, wss_port, domain). Please wait 3 minutes (180 seconds) and see if a message appears on top, in red background. Also, check if you installed correctly, and the 'Menu->Advanced->Default Settings' for SaraPhone</h3>\n";
	echo "                                        <button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"signinctrlbtn\">".$text['advanced_set_server_and_blfs']."</button> \n";
	echo "                                        <br/> \n";
	echo "                                        &nbsp; \n";
	echo "                                        <br/> \n";
	echo "                                        <div id=\"signinadv1\" align=\"center\" style=\"text-shadow:0 0px 0px rgba(0,0,0,.5);\"> \n";
	echo "                                            <table> \n";
	echo "                                                <tr> \n";
	echo "                                                   <td> <input style=\"background-color: black;\" size=25 id=\"domain\" value=\"" . $_SESSION['domain_name'] . "\" /></td><td>&nbsp;SIP&nbsp;Domain&nbsp;Name&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                   <td> <input style=\"background-color: black;\" size=25 id=\"proxy\" value=\"".$_SESSION['saraphone']['wss_proxy']['text']."\" /></td><td>&nbsp;WSS&nbsp;Proxy&nbsp;Name&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"port\" value=\"".$_SESSION['saraphone']['wss_port']['text']."\" /></td><td>&nbsp;WSS&nbsp;Proxy&nbsp;Port&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres1\" value=\"" . $rows[0]['device_key_value'] . "\" /></td><td>&nbsp;BLF1&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres1_label\" value=\"" . $rows[0]['device_key_label'] . "\" /></td><td>&nbsp;BLF1&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres2\" value=\"" . $rows[1]['device_key_value'] . "\" /></td><td>&nbsp;BLF2&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres2_label\" value=\"" . $rows[1]['device_key_label'] . "\" /></td><td>&nbsp;BLF2&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres3\" value=\"" . $rows[2]['device_key_value'] . "\" /></td><td>&nbsp;BLF3&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres3_label\" value=\"" . $rows[2]['device_key_label'] . "\" /></td><td>&nbsp;BLF3&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres4\" value=\"" . $rows[3]['device_key_value'] . "\" /></td><td>&nbsp;BLF4&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres4_label\" value=\"" . $rows[3]['device_key_label'] . "\" /></td><td>&nbsp;BLF4&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres5\" value=\"" . $rows[4]['device_key_value'] . "\" /></td><td>&nbsp;BLF5&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres5_label\" value=\"" . $rows[4]['device_key_label'] . "\" /></td><td>&nbsp;BLF5&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres6\" value=\"" . $rows[5]['device_key_value'] . "\" /></td><td>&nbsp;BLF6&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres6_label\" value=\"" . $rows[5]['device_key_label'] . "\" /></td><td>&nbsp;BLF6&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres7\" value=\"" . $rows[6]['device_key_value'] . "\" /></td><td>&nbsp;BLF7&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres7_label\" value=\"" . $rows[6]['device_key_label'] . "\" /></td><td>&nbsp;BLF7&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres8\" value=\"" . $rows[7]['device_key_value'] . "\" /></td><td>&nbsp;BLF8&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres8_label\" value=\"" . $rows[7]['device_key_label'] . "\" /></td><td>&nbsp;BLF8&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres9\" value=\"" . $rows[8]['device_key_value'] . "\" /></td><td>&nbsp;BLF9&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres9_label\" value=\"" . $rows[8]['device_key_label'] . "\" /></td><td>&nbsp;BLF9&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres10\" value=\"" . $rows[9]['device_key_value'] . "\" /></td><td>&nbsp;BLF10&nbsp;</td> \n";
	echo "                                                </tr> \n";
	echo "                                                <tr> \n";
	echo "                                                    <td> <input style=\"background-color: black;\" size=25 id=\"pres10_label\" value=\"" . $rows[9]['device_key_label'] . "\" /></td><td>&nbsp;BLF10&nbsp;".$text['label']."</td> \n";
	echo "                                                </tr> \n";
	echo "                                            </table> \n";
	echo "                                        </div> \n";
	echo "                                        <br/> \n";
	echo "                                        <button class=\"btn btn-md btn-primary btn-success\" data-inline=\"true\" id=\"loginbtn\">Login</button> \n";
	echo "                                        <br/> \n";
	// copyright
	echo "                                        <div class=\"row\"> \n";	
	echo "                                            <br/> \n";
	//echo "                                            <button class=\"btn btn-md btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel\" style=\"margin-bottom: 0px; width: 160px !important;\" onclick=\"window.location='/core/user_settings/user_dashboard.php';\">".$text['back_to_dashboard']."</button> \n";
	echo "                                            <button class=\"btn btn-md btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel2\" style=\"margin-bottom: 0px; width: 160px !important;\" >".$text['back_to_dashboard']."</button> \n";
	//echo "                                            <br/> \n";
	echo "                                            <div align=\"center\" class=\"inner\" style=\"color: #808080\"> \n";
	echo "                                                <br/> \n";
	echo "                                                <i>SaraPhone WebRTC</i> \n";
	echo "                                                <p>2020 \n";
	echo "                                                <br/>Giovanni Maruzzelli - OpenTelecom.IT</p> \n";
	echo "                                                <br/> \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	echo "                                    </div> \n";
	echo "                                </div> \n";
	echo "                            </div> \n";
	echo "                        </div> \n";
	echo "                    </div> \n";

	// display
	echo "                    <div align=\"center\" id=\"dial\" class=\"form-signin-content\"> \n";
	echo "                        <div class=\"row\"> \n";
	echo "                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                                <div id=\"webphone_body\" > \n";
	echo "                                    <div id=\"webphone_display\" > \n";
	echo "                                        <h4><span id=\"whoami\"></span></h4> \n";
	echo "                                        <div id=\"dialadv1\"> \n";
	echo "                                        </div> \n";
	echo "                                        <div style=\"max-width: 95%; margin: -20px 0px 0px 0px; white-space: nowrap; overflow: hidden; text-overflow: clip;\"> \n";
	echo "                                            <input id=\"ext\" type=\"hidden\" class=\"form-control input-lg\" placeholder=\"".$text['placeholder_number_to_dial']."\" > \n";
	echo "                                            <h2><span id=\"calling\" style=\"font-size: 20px !important;\">...</span></h2> \n";
	echo "                                            <input id=\"calling_input\" style=\"max-width: 280px;font-size: 15px !important;\" class=\"form-control input-sm\" placeholder=\"".$text['placeholder_number_to_dial']."\" >\n";
	echo "                                        </div> \n";
	echo "                                    </div> \n";
	// Incomingcall
	/******************************************************************************************************/
	/******************************************************************************************************/
	/**************************  BEGIN INCOMING CALL ONLY UNTIL ANSWERED **********************************/
	/******************************************************************************************************/
	/******************************************************************************************************/
	echo "                                    <div id=\"isIncomingcall\"> \n";
	//
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6\" id=\"webphone_keypad\"> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">1</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">2</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">3</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">4</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">5</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">6</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">7</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">8</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">9</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">.*</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">0</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\">#</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-success\" data-inline=\"true\" id=\"anscallbtn\" style=\"margin-top: 5px; margin-bottom: 5px; margin-right: 8px; width: 72px !important;\" >".$text['answer']."</button> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"rejcallbtn\" style=\"margin-top: 5px; margin-bottom: 5px; margin-right: 2px; width: 72px !important;\" >".$text['reject']."</button><br /> \n";
	echo "                                                <br/> \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";	
	// Incomingcall label
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"color: #808080\"> \n";
	echo "                                                <br/> \n";
	echo "                                                <i>SaraPhone WebRTC</i> \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	// copyright
	echo "                                        <div class=\"row\"> \n";	
	echo "                                            <div align=\"center\" class=\"inner\" style=\"color: #808080\"> \n";
	echo "                                                <p>2020 \n";
	echo "                                                <br/>Giovanni Maruzzelli - OpenTelecom.IT</p> \n";
	echo "                                           </div> \n";
	echo "                                       </div> \n";

	/******************************************************************************************************/
	/******************************************************************************************************/
	/**************************  END INCOMING CALL ONLY UNTIL ANSWERED ************************************/
	/******************************************************************************************************/
	/******************************************************************************************************/
	echo "                                    </div> \n";
	// isNotIncomingcall
	echo "                                    <div id=\"isNotIncomingcall\"> \n";
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6\" id=\"webphone_keypad\"> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext1btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio1.play ( )\">1</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext2btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio2.play ( )\">2</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext3btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio3.play ( )\">3</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext4btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio4.play ( )\">4</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext5btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio5.play ( )\">5</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext6btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio6.play ( )\">6</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext7btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio7.play ( )\">7</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext8btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio8.play ( )\">8</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext9btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio9.play ( )\">9</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"extstarbtn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio_star.play ( )\">.*</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"ext0btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio0.play ( )\">0</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"extpoundbtn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio_hash.play ( )\">#</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-success\" data-inline=\"true\" id=\"callbtn\" style=\"margin-top: 5px; margin-bottom: 5px; margin-right: 8px; width: 72px !important;\" onclick=\"audio_silence.play ( )\">".$text['dial']."</button> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"delcallbtn\" style=\"margin-top: 5px; margin-bottom: 5px; margin-right: 2px; width: 72px !important;\" onclick=\"audio_silence.play ( )\">".$text['cancel']."</button><br /> \n";
	echo "                                                <br/> \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4\" id=\"webphone_keypad_right\"> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-success\" data-inline=\"true\" id=\"redialbtn\" style=\"margin-bottom: 8px; width: 120px !important;\">".$text['redial']."</button><br /> \n";
	echo "                                                <div class=\"blinking\"><button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"unholdbtn\" style=\"margin-bottom: 8px; width: 120px !important;\">UnHold</button></div><br /> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-warning\" data-inline=\"true\" id=\"dndbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"audio1.play ( )\">".$text['dnd']."</button><br /> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-info\" data-inline=\"true\" id=\"checkvmailbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" >".$text['voicemail'].": <span id=\"vmailcount\">0/0</span></button><br /> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary\" data-inline=\"true\" id=\"phonebookbtn\" style=\"margin-bottom: 8px; width: 120px !important;\">".$text['contacts']."</button><br /> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-warning\" data-inline=\"true\" id=\"ringbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"audio1.play ( )\">".$text['mute_ring']."</button><br /> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-warning\" data-inline=\"true\" id=\"autoanswerbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"audio1.play ( )\">".$text['autoanswer']."</button><br /> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary\" data-inline=\"true\" id=\"dialctrlbtn\" style=\"margin-bottom: 8px; width: 120px !important;\">".$text['audio_microphone']."</button><br /> \n";
	//echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"window.location='/core/user_settings/user_dashboard.php';\">".$text['back_to_dashboard']."</button> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel3\" style=\"margin-bottom: 8px; width: 120px !important;\" >".$text['back_to_dashboard']."</button> \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	// isNotIncomingcall label
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"color: #808080\"> \n";
	echo "                                                <br/> \n";
	echo "                                                <i>SaraPhone WebRTC</i> \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	// copyright
	echo "                                        <div class=\"row\"> \n";	
	echo "                                            <div align=\"center\" class=\"inner\" style=\"color: #808080\"> \n";
	echo "                                                <p>2020 \n";
	echo "                                                <br/>Giovanni Maruzzelli - OpenTelecom.IT</p> \n";
	echo "                                           </div> \n";
	echo "                                       </div> \n";
	//	
	echo "                                    </div> \n";
	echo "                                </div> \n";
	echo "                            </div> \n";	
	echo "                        </div> \n";    
	// Allow Notifications
	echo "                        <button class=\"btn btn-md btn-primary btn-warn\" data-inline=\"true\" id=\"asknotificationpermission\" style=\"margin-top: 0px; margin-bottom: 8px; width: 200px !important;\">".$text['allow_notifications']."</button> \n";
	echo "                        \n";
	echo "                    </div> \n";
	// speaking
	echo "                    <div align=\"center\" id=\"incall\" class=\"form-signin-content\"> \n";
	echo "                        <div class=\"row\"> \n";
	echo "                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                                <div id=\"webphone_body\" > \n";
	echo "                                    <div id=\"webphone_display\" > \n";
	echo "                                        <h2 style=\"font-size: 20px !important;\">".$text['speaking_with'].":<br/><br/><span id=\"speakingwith\">...</span></h2> \n";
	echo "                                    </div> \n";
	echo "                                    <div id=\"dialpad\"> \n";
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6\" id=\"webphone_keypad\"> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf1btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio1.play ( )\">1</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf2btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio2.play ( )\">2</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf3btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio3.play ( )\">3</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf4btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio4.play ( )\">4</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf5btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio5.play ( )\">5</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf6btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio6.play ( )\">6</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf7btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio7.play ( )\">7</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf8btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio8.play ( )\">8</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf9btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio9.play ( )\">9</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmfstarbtn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio_star.play ( )\">.*</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmf0btn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio0.play ( )\">0</button> \n";
	echo "                                                <button class=\"btn btn-lg btn-primary btn-default\" data-inline=\"true\" id=\"dtmfpoundbtn\" style=\"margin-bottom: 5px; margin-right: 3px; width: 48px !important; height: 48px !important; font-size: 20px; font-weight: bold;\" onclick=\"audio_hash.play ( )\">#</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"hangupbtn\"     style=\"margin-top: 5px; margin-bottom: 5px; width: 67px !important;\">".$text['hangup']."</button> \n";
	echo "                                                <br/> \n";
	echo "                                                <br/> \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4\" id=\"webphone_keypad_right\"> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-warning\" data-inline=\"true\" id=\"holdbtn\" style=\"margin-bottom: 8px; width: 120px !important;\">".$text['hold']."</button><br /> \n";
	echo "                                                <button class=\"btn btn-sm btn-primary btn-warning\" data-inline=\"true\" id=\"mutebtn\" style=\"margin-bottom: 8px; width: 120px !important;\">".$text['mute']."</button><br /> \n";
	echo "                                                <button title=\"".$text['attxbtn_title']."\" class=\"btn btn-sm btn-primary\" data-inline=\"true\" id=\"attxbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"audio1.play ( )\">".$text['att_xfer']."</button><br /> \n";
	echo "                                                <button title=\"".$text['xferbtn_title']."\" class=\"btn btn-sm btn-primary btn-success\" data-inline=\"true\" id=\"xferbtn\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"audio1.play ( )\">".$text['xfer']."</button><br /> \n";
	//echo "                                                <br/> \n";
	//echo "                                                <button class=\"btn btn-sm btn-primary btn-danger\" data-inline=\"true\" id=\"gotopanel\" style=\"margin-bottom: 8px; width: 120px !important;\" onclick=\"window.location='/core/user_settings/user_dashboard.php';\">".$text['back_to_dashboard']."</button> \n";
	//echo "                                                <br/> \n";
	echo "                                            </div> \n";
	echo "                                            <div class=\"col-1 col-sm-1 col-md-1 col-lg-1 col-xl-1\"> \n";
	echo "                                                &nbsp; \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	// speaking label
	echo "                                        <div class=\"row\"> \n";
	echo "                                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"color: #808080\"> \n";
	echo "                                                <br/> \n";
	echo "                                                <i>SaraPhone WebRTC</i> \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	// copyright
	echo "                                        <div class=\"row\"> \n";	
	echo "                                            <div align=\"center\" class=\"inner\" style=\"color: #808080\"> \n";
	echo "                                                <p>2020 \n";
	echo "                                                <br/>Giovanni Maruzzelli - OpenTelecom.IT</p> \n";
	echo "                                           </div> \n";
	echo "                                       </div> \n";

	echo "                                    </div> \n";
	echo "                                </div> \n";
	echo "                            </div> \n";
	echo "                            <div align=\"center\" style=\"cacawidth: 640px;\" id=\"video1\" align=\"center\" class=\"embed-responsive embed-responsive-16by9\"> \n";
	echo "                                <video id=\"audio\" width=\"1\" autoplay=\"autoplay\" playsinline style=\"object-fit: contain;\" class=\"embed-responsive-item\"> </video> \n";
	echo "                            </div> \n";
	echo "                        </div> \n";
	echo "                    </div> \n";
	echo "                </div> \n"; // speaking with
	//
	// BLF
	//
	// with plain FreeSWITCH (eg no OpenSIPS or Kamailio) no BLF on wss webrtc 
	// see bug: https://github.com/signalwire/freeswitch/issues/398
	// use my patch.diff to FreeSWITCH and recompile 
	//
	echo "                <div class=\"col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4\"> \n";
	echo "                    <div align=\"center\" id=\"dial\" class=\"form-signin-content\"> \n";
	echo "                        <div class=\"row\"> \n";
	echo "                            <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                                <div id=\"webphone_blf\" style=\"height:auto;min-height:30px;\"> \n";
	echo "                                    <h4>".$text['blfs']."</h4> \n";




	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres1btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent1\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres2btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent2\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres3btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent3\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres4btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent4\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres5btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent5\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres6btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent6\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres7btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent7\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres8btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent8\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres9btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent9\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres10btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent10\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres11btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent11\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres12btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent12\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres13btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent13\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres14btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent14\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres15btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent15\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres16btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent16\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres17btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent17\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres18btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent18\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres19btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent19\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres20btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent20\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres21btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent21\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres22btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent22\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres23btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent23\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres24btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent24\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres25btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent25\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres26btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent26\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres27btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent27\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres28btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent28\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres29btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent29\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres30btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent30\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres31btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent31\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres32btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent32\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres33btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent33\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres34btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent34\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres35btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent35\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres36btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent36\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres37btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent37\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres38btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent38\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres39btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent39\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres40btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent40\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres41btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent41\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres42btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent42\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres43btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent43\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres44btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent44\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres45btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent45\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres46btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent46\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres47btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent47\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres48btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent48\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres49btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent49\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres50btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent50\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres51btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent51\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres52btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent52\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres53btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent53\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres54btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent54\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres55btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent55\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres56btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent56\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres57btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent57\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres58btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent58\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres59btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent59\">...</span></button>\n";

	echo "<button class=\"btn btn-sm btn-primary btn-default\" data-inline=\"true\" id=\"pres60btn\" style=\"margin-bottom: 5px; width: 150px !important;\" onclick=\"audio1.play ( )\"><span id=\"ispresent60\">...</span></button>\n";
	echo "                                </div> \n";
	echo "                            </div> \n";
	echo "                        </div> \n";
	echo "                    </div> \n";
	echo "                </div> \n";

	// Contacts 
	echo "                <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                    <div align=\"center\" class=\"form-signin\"> \n";
	echo "                        <div align=\"center\" id=\"signin\" class=\"form-signin-content\"> \n";
	echo "                            <div class=\"row\"> \n";
	echo "                                <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                                    <iframe id=\"phonebook\" src=\"contacts.php?user_extension=" . $user_extension . "\" style=\"display: none; width: 100%; height: 145px; padding: 15px; border-style: solid; border-width: 1px; border-radius: 15px; border-color: #292929; background: #333; background-image:url(\"img/bg_333.jpg\"); background-repeat: repeat; background-attachment: fixed !important;\"></iframe>\n";
	echo "                                </div> \n";
	echo "                            </div> \n";
	echo "                        </div> \n";
	echo "                    </div> \n";
	echo "                </div> \n";
	// Audio/Speaker
	echo "                <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" > \n";
	echo "                    <div align=\"center\" class=\"form-signin\"> \n";
	echo "                        <div align=\"center\" id=\"signin\" class=\"form-signin-content\"> \n";
	echo "                            <div id=\"dialadv2\"> \n";
	echo "                                <div class=\"row\"> \n";
	echo "                                    <div class=\"col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12\" style=\"margin-top: 10px;\"> \n";
	echo "                                        <div id=\"webphone_body\"> \n";
	echo "                                            <div style=\"font-size: 12px; margin-bottom: 4px; width: 100%; background-color: #333;\" id=\"listmic\"> \n";
	echo "                                            </div> \n";
	echo "                                        </div> \n";
	echo "                                    </div> \n";	
	echo "                                </div> \n";
	echo "                            </div> \n";
	echo "                        </div> \n";
	echo "                    </div> \n";
	echo "                </div> \n";
	echo "            </div> \n";
	echo "        </div> \n";
	echo "    </div> \n";
	// js
	echo "    <script type=\"text/javascript\" src=\"js/adapter.js\"></script> \n";
	echo "    <script type=\"text/javascript\" src=\"js/jquery.min.js\"></script> \n";
	echo "    <script type=\"text/javascript\" src=\"js/sip.js\"></script> \n";
	// blinking for buttons
	echo "    <script> \n";
	echo "        function blinker() { $('.blinking').fadeOut(500); $('.blinking').fadeIn(500); } \n";
	echo "        setInterval(blinker, 1000); \n";
	echo "    </script> \n";
	echo "    <script type=\"text/javascript\" src=\"saraphone.js?random=" . uniqid() . "\"></script> \n";
	echo "</div> \n";
}
echo "</body> \n";
?>
