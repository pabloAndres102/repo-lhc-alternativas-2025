<?php

$tpl = erLhcoreClassTemplate::getInstance('lhfbwhatsapp/simple_send.tpl.php');
$fbOptions = erLhcoreClassModelChatConfig::fetch('fbmessenger_options');
$data = (array)$fbOptions->data;
$item = new \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppMessage();
$item->campaign_name = '';

$instance = \LiveHelperChatExtension\fbmessenger\providers\FBMessengerWhatsAppLiveHelperChat::getInstance();

if (isset($_POST['business_account_id']) && $_POST['business_account_id'] > 0) {
    $Params['user_parameters_unordered']['business_account_id'] = (int)$_POST['business_account_id'];
}

if (isset($_GET['phone'])) {
    $phone_chat = $_GET['phone'];
    $tpl->set('phone_chat', $phone_chat);
}



if (is_numeric($Params['user_parameters_unordered']['business_account_id']) && $Params['user_parameters_unordered']['business_account_id'] > 0) {
    $account = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppAccount::fetch($Params['user_parameters_unordered']['business_account_id']);
    $instance->setAccessToken($account->access_token);
    $instance->setBusinessAccountID($account->business_account_id);
    $item->business_account_id = $account->id;
    $tpl->set('business_account_id', $account->id);
}

$templates = $instance->getTemplates();
$phones = $instance->getPhones();

if (is_numeric($Params['user_parameters_unordered']['recipient'])) {
    $contact = LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::fetch($Params['user_parameters_unordered']['recipient']);
    $item->phone = $contact->phone;
    $item->phone_whatsapp = $contact->phone_recipient;
    $item->recipient_id = $contact->id;
    $tpl->set('whatsapp_contact', $contact);
}

if (ezcInputForm::hasPostData()) {

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('fbwhatsapp/simple_send');
        exit;
    }

    $definition = array(
        'phone' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'scheduled_at' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'schedule_message' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'boolean'
        ),
        'phone_whatsapp' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'template' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'phone_sender_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'dep_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'int',
            array('min_range' => 1)
        ),
        'campaign_name' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_2' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_3' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_4' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_5' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_6' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_2' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_3' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_4' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_5' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_6' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_doc_1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'nombre_archivo1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_img_1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
        'field_header_video_1' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL,
            'unsafe_raw'
        ),
    );

    $form = new ezcInputForm(INPUT_POST, $definition);
    $Errors = array();

    if (!isset($contact)) {

        if ($form->hasValidData('phone') && $form->phone != '') {
            $item->phone = $form->phone;
        } else {
            $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger', 'Please enter a phone');
        }

        if ($form->hasValidData('phone_whatsapp') && $form->phone_whatsapp != '') {
            $item->phone_whatsapp = $form->phone_whatsapp;
        }
    }

    if ($form->hasValidData('schedule_message') && $form->schedule_message == true) {
        $item->status = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppMessage::STATUS_SCHEDULED;
    }

    if ($form->hasValidData('scheduled_at')) {
        $item->scheduled_at = strtotime($form->scheduled_at);
    }

    if ($form->hasValidData('campaign_name')) {
        $item->campaign_name = $form->campaign_name;
    }
    if ($form->hasValidData('products')) {
        $item->products = $form->products;
    }

    if ($form->hasValidData('dep_id')) {
        $item->dep_id = $form->dep_id;
    } else {
        $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger', 'Please choose a department!');
    }

    if ($form->hasValidData('phone_sender_id')) {
        $item->phone_sender_id = $form->phone_sender_id;
    } else {
        $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger', 'Please choose a send phone!');
    }

    $messageVariables = $item->message_variables_array;

    for ($i = 0; $i < 6; $i++) {
        if ($form->hasValidData('field_' . $i) && $form->{'field_' . $i}) {
            $messageVariables['field_' . $i] = $form->{'field_' . $i};
        }
    }

    for ($i = 0; $i < 6; $i++) {
        if ($form->hasValidData('field_header_' . $i) && $form->{'field_header_' . $i}) {
            $messageVariables['field_header_' . $i] = $form->{'field_header_' . $i};
        }
    }

    for ($i = 0; $i < 6; $i++) {
        if ($form->hasValidData('field_header_img_' . $i) && $form->{'field_header_img_' . $i}) {
            $messageVariables['field_header_img_' . $i] = $form->{'field_header_img_' . $i};
        }
    }

    for ($i = 0; $i < 6; $i++) {
        if ($form->hasValidData('field_header_video_' . $i) && $form->{'field_header_video_' . $i}) {
            $messageVariables['field_header_video_' . $i] = $form->{'field_header_video_' . $i};
        }
    }

    for ($i = 0; $i < 6; $i++) {
        if ($form->hasValidData('field_header_doc_' . $i) && $form->{'field_header_doc_' . $i}) {
            $messageVariables['field_header_doc_' . $i] = $form->{'field_header_doc_' . $i};
            $messageVariables['nombre_archivo' . $i] = $form->hasValidData('nombre_archivo' . $i) && $form->{'nombre_archivo' . $i} ? $form->{'nombre_archivo' . $i} : '';
        }
    }

    $messageVariablesOriginal = $messageVariables;

    if (isset($contact)) {
        $recipient = new \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaignRecipient();
        $recipient->recipient_id = $contact->id;

        foreach ($messageVariables as $key => $value) {
            $messageVariables[$key] = \erLhcoreClassGenericBotWorkflow::translateMessage($value, array('args' => ['recipient' => $recipient]));
        }
    }

    $item->message_variables_array = $messageVariables;
    $item->message_variables = json_encode($messageVariables);

    $item->business_account_id = isset($account) && is_object($account) ? $account->id : 0;

    if ($form->hasValidData('template') && $form->template != '') {
        $template = explode('||', $form->template);
        $item->template = $template[0];
        $item->language = $template[1];
    } else {
        $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger', 'Please choose a template!');
    }



    if (isset($_POST['nombre_archivo1'])) {
        $item->message_variables_array['nombre_archivo1'] =  $_POST['nombre_archivo1'];
    }





    if (isset($_POST['products'])) {
        foreach($_POST['products'] as $product){
            $item->message_variables_array[] =  $product;
        }
    }
    if (isset($_POST['offert'])) {
        $item->message_variables_array[] = $_POST['offert'];
    }
    if (isset($_POST['expiration_offert'])) {
        // Convertir la fecha de caducidad a marca de tiempo UNIX en milisegundos
        $expiration_date = new DateTime($_POST['expiration_offert']);
        $expiration_timestamp = $expiration_date->getTimestamp() * 1000;
        $item->message_variables_array[] = $expiration_timestamp;
    }
    if (isset($_POST['urlOffert'])) {
        $item->message_variables_array[] = ['urlOffert' => $_POST['urlOffert']];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['image_general'])) {
            $imageCards = $_FILES['image_general'];

            // Ahora $imageCards es un array de archivos, puedes recorrerlo
            foreach ($imageCards['tmp_name'] as $index => $name) {
                $file = $imageCards['tmp_name'][$index];

                if (!empty($file)) {
                    $archivo_bytes = file_get_contents($file);
                }

                $token = $data['whatsapp_access_token'];
                $app_id = $data['app_id'];
                $whatsapp_business_account_id = $data['whatsapp_business_account_id'];
                $mime_type = mime_content_type($file);
                if ($mime_type == 'image/webp') {
                    $image = imagecreatefromstring(file_get_contents($file));
                    $png_file = tempnam(sys_get_temp_dir(), 'converted_image_') . '.png';
                    imagepng($image, $png_file);
                    imagedestroy($image);
                    $file = $png_file;
                    $mime_type = 'image/png';
                }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v20.0/' . $data['business_phone_id'] . '/media',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array(
                        'messaging_product' => 'whatsapp',
                        'file' => new CURLFILE($file, $mime_type)
                    ),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $token,
                        'Cookie: ps_l=0; ps_n=0'
                    ),
                ));

                $response = curl_exec($curl);
                $response = json_decode($response, true);

                curl_close($curl);

                $item->message_variables_array[] = $response['id'];
                
            }
            // print_r('<mark>');
            // print_r($item->message_variables_array);
            // print_r('</mark>');
        }
    }



















    // CAROUSEL INPUTS


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item->image_ids = [];
        if (isset($_FILES['imageCard'])) {
            $imageCards = $_FILES['imageCard'];

            // Ahora $imageCards es un array de archivos, puedes recorrerlo
            foreach ($imageCards['tmp_name'] as $index => $name) {
                $file = $imageCards['tmp_name'][$index];

                if (!empty($file)) {
                    $archivo_bytes = file_get_contents($file);
                }

                $token = $data['whatsapp_access_token'];
                $app_id = $data['app_id'];
                $whatsapp_business_account_id = $data['whatsapp_business_account_id'];
                $mime_type = mime_content_type($file);
                if ($mime_type == 'image/webp') {
                    $image = imagecreatefromstring(file_get_contents($file));
                    $png_file = tempnam(sys_get_temp_dir(), 'converted_image_') . '.png';
                    imagepng($image, $png_file);
                    imagedestroy($image);
                    $file = $png_file;
                    $mime_type = 'image/png';
                }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v20.0/' . $data['business_phone_id'] . '/media',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array(
                        'messaging_product' => 'whatsapp',
                        'file' => new CURLFILE($file, $mime_type)
                    ),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $token,
                        'Cookie: ps_l=0; ps_n=0'
                    ),
                ));

                $response = curl_exec($curl);
                $response = json_decode($response, true);

                curl_close($curl);

                $item->message_variables_array[] = $response['id'];
                
            }
           
        }
    }


    if (count($Errors) == 0) {
        try {

            $item->user_id = $currentUser->getUserID();

            if ($item->status != \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppMessage::STATUS_SCHEDULED) {
                LiveHelperChatExtension\fbmessenger\providers\FBMessengerWhatsAppLiveHelperChat::getInstance()->sendTemplate($item, $templates, $phones);
                $item->saveThis();
            }

            if ($item->status == \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppMessage::STATUS_SCHEDULED) {
                $campaign = new \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaign();
                $campaign->name = $item->campaign_name != '' ? $item->campaign_name : 'Single campaign';
                $campaign->user_id = $item->user_id;
                $campaign->starts_at = $item->scheduled_at;
                $campaign->business_account_id = $item->business_account_id;
                $campaign->phone_sender_id = $item->phone_sender_id;
                $campaign->template = $item->template;
                $campaign->language = $item->language;
                $campaign->enabled = 1;
                $campaign->status = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaign::STATUS_PENDING;
                $campaign->dep_id = $item->dep_id;
                $campaign->message_variables_array = $messageVariables;
                $campaign->message_variables = json_encode($messageVariables);
                $campaign->saveThis();

                $campaignRecipient = new \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaignRecipient();

                if (isset($contact)) {
                    $campaignRecipient->type = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaignRecipient::TYPE_MAILING_LIST;
                    $campaignRecipient->recipient_id = $contact->id;
                } else {
                    $campaignRecipient->type = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaignRecipient::TYPE_MANUAL;
                    $campaignRecipient->phone = $item->phone;
                    $campaignRecipient->phone_recipient = $item->phone_whatsapp;
                }

                $campaignRecipient->user_id = $item->user_id;
                $campaignRecipient->campaign_id = $campaign->id;
                $campaignRecipient->status = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppCampaignRecipient::STATUS_PENDING;
                $campaignRecipient->saveThis();
                // print_r($campaignRecipient);
            }

            $tpl->set('updated', true);
            $fbcommand = json_encode([
                'template_name' => $item->template,
                'template_lang' => $item->language,
                'args' => $item->message_variables_array
            ]);
            $tpl->set('fbcommand', json_decode($fbcommand, true));
        } catch (Exception $e) {
            $tpl->set('errors', array($e->getMessage()));
        }
    } else {
        $tpl->set('errors', $Errors);
    }
}

if (isset($messageVariablesOriginal)) {
    $item->message_variables_array = $messageVariablesOriginal;
    $item->message_variables = json_encode($messageVariablesOriginal);
}

$tpl->setArray([
    'send' => $item,
    'templates' => $templates,
    'phones' => $phones
]);

$Result['content'] = $tpl->fetch();
echo $tpl->fetch();
exit;
