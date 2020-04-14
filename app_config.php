<?php

	//application details
		$apps[$x]['name'] = "SaraPhone";
		$apps[$x]['uuid'] = "4a085c51-7635-ff03-f67b-86e83442caca";
		$apps[$x]['category'] = "";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "http://www.fusionpbx.com";
		$apps[$x]['description']['en-us'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['en-gb'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['ar-eg'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['de-at'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['de-ch'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['de-de'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['es-cl'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['es-mx'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['fr-ca'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['fr-fr'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['he-il'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['it-it'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['nl-nl'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['pl-pl'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['pt-br'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['pt-pt'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['ro-ro'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['ru-ru'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['sv-se'] = "A WebRTC SIP WebPhone";
		$apps[$x]['description']['uk-ua'] = "A WebRTC SIP WebPhone";

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "saraphone_call";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "8f80e71a-31a5-6432-47a0-7f5a7b27caca";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "agent";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "saraphone_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;

		//default settings details
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "dbbadd02-f95d-480b-85d5-2a41cacacaca";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "saraphone";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "wss_proxy";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "192.168.1.130";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Ip Address or DNS name of WSS proxy (server)";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "dbbadd02-f95d-480b-85d5-2a41cacacacb";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "saraphone";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "wss_port";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "7443";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Port of WSS proxy (server)";
?>
