<?php

namespace Agrebnev\Oauth20\Controller;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Engine\ActionFilter;
use Agrebnev\Oauth20\Entity\ClientTable;
use Agrebnev\Oauth20\Entity\TokenTable;
use Agrebnev\Oauth20\ActionFilter\TokenAuth;
use Agrebnev\Oauth20\Traits;

class Token extends Controller
{
    use Traits\Response;

    public const TOKEN_LIVE_TIME = 3600;

    public function configureActions()
    {
        $settings = [
            'get' => [
                'prefilters' => [],
            ],
            'check' => [
                'prefilters' => [
                    new TokenAuth(),
                ],
            ],
        ];

        if ('Y' === Option::get('agrebnev.oauth20', 'controllerFilterCORS', 'Y')) {
            $settings['get']['prefilters'][] = new ActionFilter\Cors();
            $settings['check']['prefilters'][] = new ActionFilter\Cors();
        }

        if ('Y' === Option::get('agrebnev.oauth20', 'controllerFilterCSRF', 'N')) {
            $settings['get']['prefilters'][] = new ActionFilter\Csrf();
            $settings['check']['prefilters'][] = new ActionFilter\Csrf();
        }

        return $settings;
    }

    /**
     * Get token: return live token or generate new token and remove all old tokens
     * URL: /bitrix/services/main/ajax.php?action=agrebnev:oauth20.token.get
     */
    public function getAction(string $clientId, string $clientSecret, string $grant_type = '')
    {
        if ($grant_type !== 'client_credentials') {
            return $this->error(
                'invalid_grant',
                'The grant type is invalid or unsupported.',
                400
            );
        }

        /**
         * 1. Data validation
         */
        if (empty($clientId) || empty($clientSecret)) {
            return $this->error(
                'invalid_credentials',
                'Client authentication failed.',
                401
            );
        }

        /**
         * 2. Check client
         */
        $client = ClientTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=ACTIVE' => 'Y',
                '=CLIENT_ID' => $clientId,
                '=CLIENT_SECRET' => $clientSecret
            ],
            'limit' => 1
        ])->fetch();

        if (!$client) {
            return $this->error(
                'invalid_credentials',
                'Client authentication failed.',
                401
            );
        }

        $now = new DateTime();

        /**
         * 3. Check isset active token
         */
        $existingToken = TokenTable::getList([
            'select' => ['ACCESS_TOKEN', 'EXPIRES_AT'],
            'filter' => [
                '=CLIENT_ID_REF' => $client['ID'],
                '>EXPIRES_AT' => $now, // Token not expired
            ],
            'order' => ['EXPIRES_AT' => 'DESC'],
            'limit' => 1
        ])->fetch();

        if ($existingToken) {
            $expiresIn = $existingToken['EXPIRES_AT']->getTimestamp() - $now->getTimestamp();

            return [
                'access_token' => $existingToken['ACCESS_TOKEN'],
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'expires_at' => $existingToken['EXPIRES_AT']->format('c'),
                'status' => 'reused',
            ];
        }

        /**
         * 4. If empty live token — remove all old client token's
         */
        $oldTokens = TokenTable::getList([
            'select' => ['ID'],
            'filter' => ['=CLIENT_ID_REF' => $client['ID']]
        ]);
        while ($old = $oldTokens->fetch()) {
            TokenTable::delete($old['ID']);
        }

        /**
         * 5. Generate new token
         */
        $accessToken = Random::getString(64);
        $expiresIn = Option::get('agrebnev.oauth20', 'tokenLiveTime', static::TOKEN_LIVE_TIME);
        $expiresAt = (new DateTime())->add($expiresIn . ' seconds');

        $result = TokenTable::add([
            'ACCESS_TOKEN' => $accessToken,
            'CLIENT_ID_REF' => $client['ID'],
            'EXPIRES_AT' => $expiresAt,
        ]);

        if (!$result->isSuccess()) {
            return $this->error(
                'database_error',
                implode(' / ', $result->getErrorMessages()),
                401
            );
        }

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'expires_at' => $expiresAt->format('c'),
            'status' => 'issued',
        ];
    }

    /**
     * Check token
     * URL: /bitrix/services/main/ajax.php?action=agrebnev:oauth20.token.check
     */
    public function checkAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $token = $request->getHeader('Authorization');

        $tokenValue = substr($token, 7);
        $row = TokenTable::getRow([
            'filter' => [
                'ACCESS_TOKEN' => $tokenValue,
                '>=EXPIRES_AT' => new DateTime(),
            ],
        ]);

        return [
            'access_token' => $tokenValue,
            'expires_in' => $row['EXPIRES_AT']->getTimestamp() - (new DateTime())->getTimestamp(),
            'expires_at' => $row['EXPIRES_AT']->format('c'),
            'token_type' => 'Bearer'
        ];
    }
}
