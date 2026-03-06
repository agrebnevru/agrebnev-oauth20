<?php

use Bitrix\Main\Loader;
use Agrebnev\Oauth20\Entity\TokenTable;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('AGREBNEV_OAUTH20_TITLE'));

Loader::includeModule("agrebnev.oauth20");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$sTableID = "agrebnev_oauth20_tokens";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filter = [];
if (0 < (int)$request->getQuery('clientId')) {
    $filter['CLIENT_ID_REF'] = (int)$request->getQuery('clientId');
}

$rsData = TokenTable::getList([
    'filter' => $filter,
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
        "id" => "ACCESS_TOKEN",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__ACCESS_TOKEN'),
        "sort" => "ACCESS_TOKEN",
        "default" => true
    ],
    [
        "id" => "EXPIRES_AT",
        "content" => Loc::getMessage('AGREBNEV_OAUTH20_TBL_HEADER__EXPIRES_AT'),
        "default" => true
    ],
]);

while ($arRes = $rsData->NavNext(true, "f_")) {
    $row =& $lAdmin->AddRow($f_ID, $arRes);
}

$lAdmin->AddAdminContextMenu(
    [
        [
            "TEXT" => Loc::getMessage('AGREBNEV_OAUTH20_CONTEXT_MENU__GO_CLIENTS'),
            "LINK" => "agrebnev_oauth20_clients.php?lang=" . LANG,
            "ICON" => "btn_list"
        ]
    ]
);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
