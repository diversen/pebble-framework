<?php

declare(strict_types=1);

namespace Pebble;

use Aidantwoods\SecureHeaders\SecureHeaders;
use Pebble\Service\ConfigService;

/**
 * A wrapper class for sending CSP headers defined in a 'CSP' config file
 * An example of a configuration file can be viewed at:
 *
 * https://github.com/diversen/ppm-project-manager/blob/main/config/CSP.php
 */
class CSP
{
    private string $nonce = '';

    public function __construct()
    {
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function sendCSPHeaders(): void
    {
        $config = (new ConfigService())->getConfig();

        if (!$config->get("CSP.enabled")) {
            $this->nonce = "Nonce not enabled";
            return;
        }

        $this->nonce = $config->get('CSP.nonce');
        $headers = $config->get('CSP.headers');
        $headers->apply();
    }
}
