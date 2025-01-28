<?php

header ( 'content-type: application/json; charset=utf-8' );

if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
    echo erLhcoreClassChat::safe_json_encode(array('error' => true, 'token' => $currentUser->getCSFRToken(), 'msg' => 'Try again or refresh a page. We could not verify your request.' ));
    exit;
}

$page = erLhcoreClassModelMyFBPage::findOne(['filter' => [
    'page_id' => 0,
    'whatsapp_business_account_id' => $Params['user_parameters']['whatsapp_business_account_id'],
    'whatsapp_business_phone_number_id' => $Params['user_parameters']['phone_number_id'],
]]);

if (is_object($page) && $page->enabled == 1) {
    $page->dep_id = $Params['user_parameters']['dep_id'];
    $page->updateThis(['update' => ['dep_id']]);
    echo erLhcoreClassChat::safe_json_encode(array('updated' => true, 'msg' => 'Department was updated!' ));
}

exit;
?>