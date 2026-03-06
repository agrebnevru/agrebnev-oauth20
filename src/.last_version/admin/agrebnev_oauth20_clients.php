<?php

use Bitrix\Main\Loader;
use Agrebnev\Oauth20\Entity\ClientTable;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('AGREBNEV_OAUTH20_TITLE'));

Loader::includeModule("agrebnev.oauth20");

$sTableID = "agrebnev_oauth20_clients";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && check_bitrix_sessid()) {
    foreach ($arID as $ID) {
        if ($ID > 0) {
            ClientTable::delete($ID);
        }
    }
}

$rsData = ClientTable::getList([
    'order' => [strtoupper($by) => strtoupper($order)]
]);
$rsData = new CAdminUiResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->SetNavigationParams($rsData);

$lAdmin->AddHeaders([
    [
        "id" => "ID",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__ID'),
        "sort" => "ID",
        "default" => true
    ],
    [
        "id" => "DATE_INSERT",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__DATE_INSERT'),
        "sort" => "DATE_INSERT",
        "default" => true
    ],
    [
        "id" => "TIMESTAMP_X",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__TIMESTAMP_X'),
        "sort" => "TIMESTAMP_X",
        "default" => true
    ],
    [
        "id" => "ACTIVE",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__ACTIVE'),
        "sort" => "ACTIVE",
        "default" => true
    ],
    [
        "id" => "NAME",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__NAME'),
        "sort" => "NAME",
        "default" => true
    ],
    [
        "id" => "CLIENT_ID",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__CLIENT_ID'),
        "sort" => "CLIENT_ID",
        "default" => true
    ],
    [
        "id" => "CLIENT_SECRET",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__CLIENT_SECRET'),
        "default" => true
    ],
]);

while ($arRes = $rsData->NavNext(true, "f_")) {
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $editUrl = "agrebnev_oauth20_client_edit.php?ID=" . $f_ID . "&lang=" . LANG;
    $row->AddViewField("ID", '<a href="' . $editUrl . '">' . $f_ID . '</a>');
    $row->AddViewField(
        "ACTIVE",
        'Y' === $f_ACTIVE ? Loc::getMessage('AGREBNEV_OAUTH20_TBL_ROW_ACTIVE_Y') : Loc::getMessage(
            'AGREBNEV_OAUTH20_TBL_ROW_ACTIVE_N'
        )
    );
    $row->AddViewField("NAME", '<a href="' . $editUrl . '">' . $f_NAME . '</a>');

    $arActions = [
        [
            "ICON" => "edit",
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_ACTION_MENU__EDIT'),
            "ACTION" => $lAdmin->ActionRedirect("agrebnev_oauth20_client_edit.php?ID=" . $f_ID)
        ],
        ["SEPARATOR" => true],
        [
            "ICON" => "delete",
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_ACTION_MENU__DELETE'),
            "ACTION" => "if(confirm('" . Loc::getMessage(
                    'AGREBNEV_OAUTH20_TBL_ACTION_MENU__DELETE__CONFIRM'
                ) . "')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
        ],
        ["SEPARATOR" => true],
        [
            "ICON" => "",
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_ACTION_MENU__TOKEN_LIST'),
            "ACTION" => $lAdmin->ActionRedirect("agrebnev_oauth20_tokens.php?clientId=" . $f_ID)
        ],
    ];
    $row->AddActions($arActions);
}

$lAdmin->AddAdminContextMenu(
    [
        [
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU__ADD'),
            "LINK" => "agrebnev_oauth20_client_edit.php?lang=" . LANG,
            "ICON" => "btn_new"
        ],
        [
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU__TOKENS'),
            "LINK" => "agrebnev_oauth20_tokens.php?lang=" . LANG,
            "ICON" => "btn_list"
        ],
    ]
);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
