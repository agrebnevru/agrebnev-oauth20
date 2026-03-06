<?php

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

return [
    "parent_menu" => "global_menu_services", // Раздел "Сервисы"
    "section" => "agrebnev_oauth20",
    "sort" => 9999, // Максимальное число, чтобы пункт был в самом низу
    "text" => \Bitrix\Main\Localization\Loc::getMessage("AGREBNEV_OAUTH20_MENU_TEXT"),
    "title" => \Bitrix\Main\Localization\Loc::getMessage("AGREBNEV_OAUTH20_MENU_TEXT"),
    "icon" => "security_menu_icon", // Стандартная иконка безопасности
    "page_icon" => "security_page_icon",
    "items_id" => "menu_ag_oauth20",
    "items" => [
        [
            "text" => \Bitrix\Main\Localization\Loc::getMessage("AGREBNEV_OAUTH20_MENU_CLIENT_LIST__TEXT"),
            "url" => "agrebnev_oauth20_clients.php?lang=" . LANGUAGE_ID,
            "more_url" => ["agrebnev_oauth20_client_edit.php", "agrebnev_oauth20_tokens.php"],
            "title" => "",
        ],
    ],
];
