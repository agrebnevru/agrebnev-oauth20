<?php

namespace Agrebnev\Oauth20\ActionFilter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Agrebnev\Oauth20\Entity\TokenTable;
use Agrebnev\Oauth20\Traits;

class TokenAuth extends Base
{
    use Traits\Response;

    public function onBeforeAction(\Bitrix\Main\Event $event)
    {
        $request = Context::getCurrent()->getRequest();
        $authHeader = $request->getHeader('Authorization');

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return $this->error(
                'invalid_token',
                'Unauthorized: Missing or invalid token format.',
                401
            );
        }

        $tokenValue = substr($authHeader, 7);
        $token = TokenTable::getList([
            'filter' => [
                '=ACCESS_TOKEN' => $tokenValue,
                '>=EXPIRES_AT' => new DateTime(),
            ]
        ])->fetch();

        if (!$token) {
            return $this->error(
                'invalid_token',
                'The access token expired or is invalid.',
                401
            );
        }

        return null;
    }
}
