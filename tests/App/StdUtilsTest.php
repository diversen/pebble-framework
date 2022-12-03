<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pebble\App\StdUtils;
use Pebble\Config;

final class StdUtilsTest extends TestCase
{
    public function test_getInstancesOfServices(): void
    {
        $std_utils = new StdUtils();


        // Test if same instances
        $acl = $std_utils->getACL();
        $this->assertSame($acl, $std_utils->getACL());

        $acl_role = $std_utils->getACLRole();
        $this->assertSame($acl_role, $std_utils->getACLRole());

        $auth = $std_utils->getAuth();
        $this->assertSame($auth, $std_utils->getAuth());

        $config = $std_utils->getConfig();
        $this->assertSame($config, $std_utils->getConfig());

        $db = $std_utils->getDB();
        $this->assertSame($db, $std_utils->getDB());

        $flash = $std_utils->getFlash();
        $this->assertSame($flash, $std_utils->getFlash());

        $json = $std_utils->getJSON();
        $this->assertSame($json, $std_utils->getJSON());

        $log = $std_utils->getLog();
        $this->assertSame($log, $std_utils->getLog());

        $template = $std_utils->getTemplate();
        $this->assertSame($template, $std_utils->getTemplate());

        // Make sure they are not the same instance
        // Only migrate up or down in the same instance
        // Therefor, we need to make sure they are not the same instance
        $migration = $std_utils->getMigration();
        $this->assertNotSame($migration, $std_utils->getMigration());

        $template = $std_utils->getTemplate();
        $this->assertSame($template, $std_utils->getTemplate());

        // Different objects
        $another_config = new Config();
        $this->assertNotSame($config, $another_config);
    }
}
