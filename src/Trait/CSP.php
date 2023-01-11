<?php

declare(strict_types=1);

namespace Pebble\Trait;

use Aidantwoods\SecureHeaders\SecureHeaders;
use Pebble\Service\ConfigService;

/**
 * A trait for sending CSP headers defined in a 'CSP' config file
 * You may want to add this to a Main class in order to send CSP headers
 */
trait CSP
{
    private static $nonce;
    public static function getNonce()
    {
        return self::$nonce;
    }

    public function sendCSPHeaders()
    {
        $config = (new ConfigService())->getConfig();

        if (!$config->get("CSP.enabled")) {
            return;
        }

        self::$nonce = $config->get('CSP.nonce');

        /**
         * @var SecureHeaders $headers
         */
        $headers = $config->get('CSP.headers');
        $headers->apply();
    }
}
