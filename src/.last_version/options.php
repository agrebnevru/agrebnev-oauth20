<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @global string $mid
 */

Loc::loadMessages(__FILE__);

$request = Application::getInstance()->getContext()->getRequest();

$tabs = [];
$tabs[] = [
    'DIV' => 'agrebnev_oauth20_tab_settings',
    'TAB' => Loc::getMessage('AGREBNEV_OAUTH20_TAB_NAME'),
    'ICON' => '',
    'TITLE' => Loc::getMessage('AGREBNEV_OAUTH20_TAB_TITLE'),
];

$allOptions = [];

/************************ tab ***************************/
$allOptions['agrebnev_oauth20_tab_settings'][] = [
    'replaceErrorAnswerForRFC6749',
    Loc::getMessage('AGREBNEV_OAUTH20_OPTIONS_REPLACE_RFC6749'),
    'N',
    ['checkbox', 'Y']
];
$allOptions['agrebnev_oauth20_tab_settings'][] = [
    'tokenLiveTime',
    Loc::getMessage('AGREBNEV_OAUTH20_OPTIONS_TOKEN_LIVE_TIME'),
    3600,
    ['text']
];
/************************ tab ***************************/

if (
    (isset($_REQUEST['save']) || isset($_REQUEST['apply']))
    && check_bitrix_sessid()
) {
    __AdmSettingsSaveOptions($mid, $allOptions['agrebnev_oauth20_tab_settings']);

    LocalRedirect('settings.php?mid=' . $mid . '&lang=' . LANG);
}

$tabControl = new CAdminTabControl('tabControl', $tabs);

?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>"
      name="agrebnev_wi_settings"><?php
    echo bitrix_sessid_post();

    $tabControl->Begin();

    $tabControl->BeginNextTab();
    __AdmSettingsDrawList($mid, $allOptions['agrebnev_oauth20_tab_settings']);

    $tabControl->Buttons([]);
    $tabControl->End();

    ?></form>
