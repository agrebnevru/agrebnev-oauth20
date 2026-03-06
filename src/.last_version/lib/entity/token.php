<?php

namespace Agrebnev\Oauth20\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\Type\DateTime;

class TokenTable extends DataManager
{
    public static function getTableName()
    {
        return 'agrebnev_oauth20_token';
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
            new StringField('ACCESS_TOKEN', ['required' => true]),
            new IntegerField('CLIENT_ID_REF', ['required' => true]),
            new DatetimeField('EXPIRES_AT', ['required' => true]),
        ];
    }

    public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
    {
        $result = new \Bitrix\Main\ORM\EventResult;
        $result->modifyFields(['TIMESTAMP_X' => new DateTime()]);
        return $result;
    }
}
