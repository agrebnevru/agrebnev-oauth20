<?php

namespace Agrebnev\Oauth20\Traits;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\EventResult;

trait Response
{
    public const AGR_OAUTH20_MODULE_ID = 'agrebnev.oauth20';

    public function error(string $error, string $errorDescription, int $statusCode = 400): EventResult
    {
        if ('N' === Option::get('agrebnev.oauth20', 'replaceErrorAnswerForRFC6749', 'N')) {
            $this->addError(new Error($error, $errorDescription));
            return new EventResult(EventResult::ERROR, null, static::AGR_OAUTH20_MODULE_ID, $this);
        }

        /**
         * RFC 6749
         */
        $response = \Bitrix\Main\Context::getCurrent()->getResponse();
        $response->addHeader('Content-Type', 'application/json');
        $response->setStatus($statusCode);
        echo \Bitrix\Main\Web\Json::encode([
            'error' => $error,
            'error_description' => $errorDescription,
        ]);
        \Bitrix\Main\Application::getInstance()->terminate();
    }
}
