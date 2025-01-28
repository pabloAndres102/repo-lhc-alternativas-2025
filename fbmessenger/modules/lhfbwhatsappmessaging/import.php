<?php

$currentUser = erLhcoreClassUser::instance();
$currentUser->getUserID();

if (isset($_GET['sample'])){
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename=contact-sample.csv");
    header("Content-Transfer-Encoding: binary");
    echo file_get_contents('extension/fbmessenger/doc/contact.csv');
    exit;
}

$tpl = erLhcoreClassTemplate::getInstance('lhfbwhatsappmessaging/import.tpl.php');

$itemDefault = new LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact();

if (is_array($Params['user_parameters_unordered']['ml'])) {
    $itemDefault->ml_ids_front = $Params['user_parameters_unordered']['ml'];
}

if (isset($_POST['remove_old']) && $_POST['remove_old'] == true) {
    $tpl->set('remove_old', true);
}

if (isset($_POST['UploadFileAction'])) {

    $itemDefault->ml_ids_front = isset($_POST['ml']) && !empty($_POST['ml']) ? $_POST['ml'] : [];

    $errors = [];

    if (empty($itemDefault->ml_ids_front)) {
        $errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger','Please choose at-least one mailing list!');
    }

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        $errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger','Missing CSRF Token!!');
    }

    if (empty($errors) && erLhcoreClassSearchHandler::isFile('files',array('csv'))) {

        $dir = 'var/tmpfiles/';
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('theme.temppath', array('dir' => & $dir));

        erLhcoreClassFileUpload::mkdirRecursive( $dir );

        $filename = erLhcoreClassSearchHandler::moveUploadedFile('files', $dir);

        $header = NULL;
        $data = array();

        $canned = [
            'phone',
            'phone_recipient',
            'name',
            'lastname',
            'title',
            'company',
            'email',
            'date',
            'status',
            'attr_str_1',
            'attr_str_2',
            'attr_str_3',
            'attr_str_4',
            'attr_str_5',
            'attr_str_6',
            'file_1',
            'file_2',
            'file_3',
            'file_4'
        ];
        if (($handle = fopen($dir . $filename, 'r')) !== FALSE)
        {
            // Leemos el contenido completo del archivo
            $fileContent = fread($handle, filesize($dir . $filename));

            // Reemplazamos las comas por puntos y comas
            $fileContent = str_replace(',', ';', $fileContent);

            // Convertimos el contenido modificado en un flujo de datos temporal en memoria
            $modifiedHandle = fopen('php://memory', 'w+');
            fwrite($modifiedHandle, $fileContent);
            rewind($modifiedHandle);

            while (($row = fgetcsv($modifiedHandle, 10000, ";")) !== FALSE)
            {
                if(!$header) {
                    $header = $row;
                } else {
                    if (count($header) != count($row)) {
                        $row = $row + array_fill(count($row),count($header) - count($row),'');
                    }
                    $data[] = array_combine($header, $row);
                }
            }

            fclose($modifiedHandle);
            fclose($handle);
        }

        unlink($dir . $filename);


        $stats = array(
            'updated' => 0,
            'imported' => 0,
            'removed' => 0,
        );

        $statusMap = [
            'unknown' => \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::DELIVERY_STATUS_UNKNOWN,
            'unsubscribed' => \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::DELIVERY_STATUS_UNSUBSCRIBED,
            'failed' => \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::DELIVERY_STATUS_FAILED,
            'active' => \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::DELIVERY_STATUS_ACTIVE
        ];


        if ($canned === $header) {
            if (isset($_POST['remove_old']) && $_POST['remove_old'] == true && !empty($itemDefault->ml_ids_front)) {
                foreach (LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContactList::getList(array('filterin' => ['mailing_list_id' => $itemDefault->ml_ids_front], 'limit' => false)) as $oldAssignment) {
                    if (is_object($oldAssignment->mailing_recipient)) {
                        $oldAssignment->mailing_recipient->removeThis();
                    }
                    $stats['removed']++;
                }
            }

            foreach ($data as $item) {

                $cannedMessage = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::findOne(array('filter' => array('phone' => $item['phone'])));

                if (!($cannedMessage instanceof \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact)) {
                    $cannedMessage = new \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact();
                    $cannedMessage->user_id = $currentUser->getUserID();
                    $stats['imported']++;
                } else {
                    $stats['updated']++;
                }

                $cannedMessage->ml_ids = array_unique(array_merge($itemDefault->ml_ids_front, $cannedMessage->ml_ids_front));

                $cannedMessage->setState($item);

                if (isset($statusMap[$item['status']])){
                    $cannedMessage->delivery_status = $statusMap[$item['status']];
                } else {
                    $cannedMessage->delivery_status = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::DELIVERY_STATUS_UNKNOWN;
                }

                $cannedMessage->saveThis();

                if ($cannedMessage->isAllPrivateListMember() === true) {
                    $cannedMessage->private = \LiveHelperChatExtension\fbmessenger\providers\erLhcoreClassModelMessageFBWhatsAppContact::LIST_PRIVATE;
                    $cannedMessage->updateThis(['update' => ['private']]);
                }
                
            }

            $tpl->set('update', $stats);
        } else {
            $tpl->set('errors', [erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger','Expected columns does not match!')]);
        }

    } elseif (!empty($errors)) {
        $tpl->set('errors', $errors);
    } else {
        $tpl->set('errors', [erTranslationClassLhTranslation::getInstance()->getTranslation('module/fbmessenger','Invalid file format')]);
    }
}

$tpl->set('item', $itemDefault);

$Result['content'] = $tpl->fetch();
$Result['pagelayout'] = 'popup';

?>