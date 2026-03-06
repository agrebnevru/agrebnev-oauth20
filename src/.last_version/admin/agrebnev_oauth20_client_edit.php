<?php

use Agrebnev\Oauth20\Entity\ClientTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

Loader::includeModule("agrebnev.oauth20");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$ID = (int)$request->getQuery('ID');
$message = null;

if (
    $request->getRequestMethod() == "POST"
    && ($_POST["save"] <> "" || $_POST["apply"] <> "")
    && check_bitrix_sessid()
) {
    $fields = [
        'ACTIVE' => $request->getPost('ACTIVE'),
        'NAME' => $request->getPost('NAME'),
        'CLIENT_ID' => $request->getPost('CLIENT_ID'),
        'CLIENT_SECRET' => $request->getPost('CLIENT_SECRET'),
    ];

    if ($ID > 0) {
        $result = ClientTable::update($ID, $fields);
    } else {
        $result = ClientTable::add($fields);
        if ($result->isSuccess()) {
            $ID = $result->getId();
        }
    }

    if ($result->isSuccess()) {
        if ($_POST["save"] <> "") {
            LocalRedirect("agrebnev_oauth20_clients.php?lang=" . LANG);
        } else {
            LocalRedirect("agrebnev_oauth20_client_edit.php?ID=" . $ID . "&lang=" . LANG);
        }
    } else {
        $message = new CAdminMessage(
            Loc::getMessage('AGREBNEV_OAUTH20_ERROR_SAVE'),
            new \Bitrix\Main\Error(implode("<br>", $result->getErrorMessages()))
        );
    }
}

$data = ($ID > 0) ? ClientTable::getById($ID)->fetch() : [];

$APPLICATION->SetTitle(
    $ID > 0 ? Loc::getMessage('AGREBNEV_OAUTH20_EDIT_CLIENT') . $ID : Loc::getMessage('AGREBNEV_OAUTH20_NEW_CLIENT')
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if ($message) {
    echo $message->Show();
}

$menu = [
    [
        "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU_TO_LIST_TEXT'),
        "TITLE" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU_TO_LIST_TITLE'),
        "LINK" => "agrebnev_oauth20_clients.php?lang=" . LANG,
        "ICON" => "btn_list",
    ],
];
if (0 < count($data)) {
    $menu[] = [
        "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU_TO_TOKEN_LIST_TEXT'),
        "TITLE" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU_TO_TOKEN_LIST_TITLE'),
        "LINK" => "agrebnev_oauth20_tokens.php?clientId=" . $data['ID'] . "&lang=" . LANG,
        "ICON" => "",
    ];
}

$context = new CAdminContextMenu($menu);
$context->Show();

?>
    <form method="POST" action="<?php
    echo $APPLICATION->GetCurPage() ?>?lang=<?= LANG ?>&ID=<?= $ID ?>">
        <?= bitrix_sessid_post() ?>
        <?php
        $tabControl = new CAdminTabControl(
            "tabControl",
            [
                [
                    "DIV" => "edit1",
                    "TAB" => Loc::getMessage('AGREBNEV_OAUTH20_TAB_NAME'),
                    "ICON" => "main_user_edit",
                    "TITLE" => Loc::getMessage('AGREBNEV_OAUTH20_TAB_TITLE'),
                ]
            ]
        );
        $tabControl->Begin();
        $tabControl->BeginNextTab(); ?>
        <?php
        if (0 < count($data)): ?>
            <tr>
                <td width="40%"><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__ID') ?>:</td>
                <td><?= $data["ID"] ?></td>
            </tr>
            <tr>
                <td width="40%"><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__DATE_INSERT') ?>:</td>
                <td><?= $data["DATE_INSERT"]->toString() ?></td>
            </tr>
            <tr>
                <td width="40%"><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__TIMESTAMP_X') ?>:</td>
                <td><?= $data["TIMESTAMP_X"]->toString() ?></td>
            </tr>
        <?php
        endif; ?>
        <tr>
            <td width="40%"><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__ACTIVE') ?>:</td>
            <td>
                <input type="hidden" name="ACTIVE" value="N">
                <input type="checkbox" name="ACTIVE"
                       value="Y" <?= ('Y' === trim($data["ACTIVE"]) || '' === trim(
                    (string)$data["ACTIVE"]
                ) ? ' checked="checked"' : '') ?>>
            </td>
        </tr>
        <tr>
            <td width="40%"><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__NAME') ?>:</td>
            <td><input type="text" name="NAME" value="<?= $data["NAME"] ?>" size="50"></td>
        </tr>
        <tr>
            <td><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__CLIENT_ID') ?>:</td>
            <td><input type="text" name="CLIENT_ID" value="<?= $data["CLIENT_ID"] ?>" size="50"> <?= Loc::getMessage(
                    'AGREBNEV_OAUTH20_FORM__CLIENT_ID__NOTE'
                ) ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__CLIENT_SECRET') ?>:</td>
            <td><input type="text" name="CLIENT_SECRET" value="<?= $data["CLIENT_SECRET"] ?>"
                       size="50"> <?= Loc::getMessage('AGREBNEV_OAUTH20_FORM__CLIENT_SECRET__NOTE') ?>
            </td>
        </tr>
        <?php
        $tabControl->Buttons(["back_url" => "agrebnev_oauth20_clients.php?lang=" . LANG]);
        $tabControl->End(); ?>
    </form>
<?php

echo BeginNote();
echo Loc::getMessage('AGREBNEV_OAUTH20_FORM__NOTE');
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
