<?php

namespace Agrebnev\Oauth20\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Security\Random;

class ClientTable extends DataManager
{
    public static function getTableName()
    {
        return 'agrebnev_oauth20_client';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new DatetimeField('DATE_INSERT', [
                'default_value' => function () {
                    return new DateTime();
                },
            ]),
            new DatetimeField('TIMESTAMP_X', [
                'default_value' => function () {
                    return new DateTime();
                },
            ]),
            new BooleanField('ACTIVE', ['values' => ['N', 'Y'], 'default_value' => 'Y',]),
            new StringField('CLIENT_ID', ['required' => true, 'unique' => true]),
            new StringField('CLIENT_SECRET', ['required' => true]),
            new StringField('NAME'),
        ];
    }

    public static function onBeforeAdd(Event $event)
    {
        $result = new \Bitrix\Main\ORM\EventResult();
        $data = $event->getParameter("fields");
        $modify = [];

        // Автогенерация CLIENT_ID (если не задан)
        if (empty($data['CLIENT_ID'])) {
            $modify['CLIENT_ID'] = 'client_' . Random::getString(12);
        }

        // Автогенерация CLIENT_SECRET (если не задан)
        if (empty($data['CLIENT_SECRET'])) {
            $modify['CLIENT_SECRET'] = Random::getString(32);
        }

        if (!empty($modify)) {
            $result->modifyFields($modify);
        }

        return $result;
    }

    public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
    {
        $result = new \Bitrix\Main\ORM\EventResult;
        $result->modifyFields(['TIMESTAMP_X' => new DateTime()]);
        return $result;
    }
}
