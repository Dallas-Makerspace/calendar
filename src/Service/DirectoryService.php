<?php
namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use LdapRecord\Connection;

/**
 * Read-only Active Directory lookups.
 *
 * Uses the same service-account bind pattern as the rest of the application.
 * In tests the LDAP network round-trip is replaced by a stub map configured
 * via `Directory.stubGroups`, so unit/integration tests never touch the
 * network.
 */
class DirectoryService
{
    /**
     * Check whether a group with the given cn exists in Active Directory.
     *
     * The comparison is case-insensitive, matching AD's case-insensitive cn.
     *
     * @param string $cn The group cn to look up.
     * @return bool True when a group object with that cn exists.
     * @throws \App\Service\DirectoryUnavailableException When AD cannot be reached.
     */
    public static function groupExists($cn)
    {
        $stub = Configure::read('Directory.stubGroups');
        if (is_array($stub)) {
            foreach ($stub as $name) {
                if (strcasecmp((string)$name, (string)$cn) === 0) {
                    return true;
                }
            }

            return false;
        }

        try {
            $cfg = Configure::read('ActiveDirectory');
            // ext-ldap constants are only defined when the extension is
            // present; guard so a missing extension still fails closed below
            // instead of warning here.
            $options = [];
            if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
                $options[LDAP_OPT_NETWORK_TIMEOUT] = 5;
            }
            if (defined('LDAP_OPT_X_TLS_REQUIRE_CERT')) {
                $options[LDAP_OPT_X_TLS_REQUIRE_CERT] = LDAP_OPT_X_TLS_NEVER;
            }
            $connection = new Connection([
                'hosts' => $cfg['domain_controllers'],
                'base_dn' => $cfg['base_dn'],
                'username' => $cfg['admin_username'],
                'password' => $cfg['admin_password'],
                'version' => 3,
                'use_tls' => isset($cfg['use_tls']) ? $cfg['use_tls'] : false,
                'options' => $options,
            ]);
            $connection->connect();

            $record = $connection->query()->where([
                ['cn', '=', $cn],
                ['objectclass', '=', 'group'],
            ])->first();

            return $record !== null;
        } catch (\Throwable $e) {
            Log::warning(sprintf(
                'DirectoryService: LDAP group lookup failed for cn="%s": %s',
                $cn,
                $e->getMessage()
            ));
            throw new DirectoryUnavailableException(
                'Active Directory is unreachable.',
                0,
                $e
            );
        }
    }
}
