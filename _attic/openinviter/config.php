<?php
#define("REMOTE_CONNECTION_METHOD_FSOCK", "NO");
#define("CURLPROXY","proxy.eleks.lviv.ua:3128");
$openinviter_settings=array(
'username'=>'edesign', 'private_key'=>'a9e07fbdd98816c2c2882759fc601328', 'cookie_path'=>dirname(__FILE__).'/tmp', 'message_body'=>'You are invited to www.site.com', 'message_subject'=>' is inviting you to www.site.com', 'transport'=>'curl', 'local_debug'=>'on_error', 'remote_debug'=>'');
?>