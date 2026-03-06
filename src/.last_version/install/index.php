<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Agrebnev\Oauth20\Entity\ClientTable;
use Agrebnev\Oauth20\Entity\TokenTable;

Loc::loadMessages(__FILE__);

class agrebnev_oauth20 extends CModule
{
    public $MODULE_ID = 'agrebnev.oauth20';
    public $MODULE_VERSION = '0.0.1';
    public $MODULE_VERSION_DATE = '2026-02-19';
    public $MODULE_NAME = 'OAuth 2.0 Auth Module';
    public $MODULE_DESCRIPTION = 'OAuth 2.0 based on Bitrix D7';
    public $PARTNER_NAME = 'Alex Grebnev';
    public $PARTNER_URI = 'https://agrebnev.ru/';

    public function __construct()
    {
        $arModuleVersion = [];

        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('AGREBNEV_OAUTH20_INSTALL_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('AGREBNEV_OAUTH20_INSTALL_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('AGREBNEV_OAUTH20_INSTALL_COPMPANY_NAME');
        $this->PARTNER_URI = 'https://agrebnev.ru/';
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
    }

    public function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        UnRegisterModule($this->MODULE_ID);
    }

    public function InstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            if (!Application::getConnection()->isTableExists(ClientTable::getTableName())) {
                ClientTable::getEntity()->createDbTable();
            }
            if (!Application::getConnection()->isTableExists(TokenTable::getTableName())) {
                TokenTable::getEntity()->createDbTable();
            }
        }
    }

    public function UnInstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getConnection();
            $connection->dropTable(TokenTable::getTableName());
            $connection->dropTable(ClientTable::getTableName());
        }
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            \Bitrix\Main\Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin',
            \Bitrix\Main\Application::getDocumentRoot() . '/bitrix/admin'
        );
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(
            \Bitrix\Main\Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin/',
            \Bitrix\Main\Application::getDocumentRoot() . '/bitrix/admin'
        );
    }
}
