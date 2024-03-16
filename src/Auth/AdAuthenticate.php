<?php
namespace App\Auth;

use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use LdapRecord\Models\ActiveDirectory\Entry;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\ActiveDirectory\Group;
use LdapRecord\Models\ModelNotFoundException;
use LdapRecord\Container;
use LdapRecord\Connection;


class AdAuthenticate extends FormAuthenticate
{
    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry
     *   used on this request.
     * @param array $config Array of config to use.
     */
    public function __construct(ComponentRegistry $registry, $config)
    {
        $this->registry = $registry;
        $this->config = $config['config'];

        // Connecting with an an account...
        $this->connection = new \LdapRecord\Connection([
            'hosts' => $this->config['domain_controllers'],
            'base_dn' => $this->config['base_dn'],
            'username' => $this->config['admin_username'],
            'password' => $this->config['admin_password'],
            'use_tls' => $this->config['use_tls'],
            'version' => 3,
            'options' => [
                // See: http://php.net/ldap_set_option
                LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_NEVER
            ]
        ]);

        Container::addConnection($this->connection);
    }


    /**
     * Create a friendly, formatted groups array
     *
     * @param array $memberships Array of memberships to create a friendly array for.
     * @return array An array of friendly group names.
     */
    protected function _cleanGroups($memberships)
    {
        $groups = [];
        foreach ($memberships as $group) {
            $groups[] = $group->cn;
        }

        return $groups;
    }

    /**
     * Authenticate user
     *
     * @param \Cake\Network\Request $request The request that contains login information.
     * @param \Cake\Network\Response $response Unused response object.
     * @return mixed False on login failure. An array of User data on success.
     */
    public function authenticate(Request $request, Response $response)
    {
        if( !array_key_exists("username",$request->data) || !array_key_exists("password",$request->data )){
            return false;
        }

        return $this->findAdUser(
            $request->data['username'],
            $request->data['password']
        );
    }

    /**
     * Connect to Active Directory on behalf of a user and return that user's data.
     *
     * @param string $username The username (samaccountname).
     * @param string $password The password.
     * @return mixed False on failure. An array of user data on success.
     */
    public function findAdUser($username, $password)
    {
        try
        {
            $user = User::findByOrFail($this->config['username_field'], trim($username));
            /*$user = $this->connection->query()
                ->where($this->config['username_field'], '=',$username)
                ->firstOrFail();*/

            if ( $this->connection->auth()->attempt($user->getDn(), $password))
            {
                $results = [];
                $results['ssologin'] = false;
                $attributes = $user->getAttributes();
                foreach ($attributes as $key => $value){
                    if(is_array($value) && count($value) == 1)
                    {
                        $results[$key] = $value[0];
                    }
                    else
                    {
                        $results[$key] = $value;
                    }
                }
                $groups = $user->groups()->get();
                $results['groups'] = [];
                foreach ($groups as $g) {
                    $results['groups'][] = $g->cn[0];
                }
                return $results;
            }

            return false;
        } catch (ModelNotFoundException $ex) {
            // Incase we don't find the user in AD
            return false;
        } catch (Exception $ex) {
            throw new \RuntimeException('Failed to bind to LDAP server. Check Auth configuration settings.');
        }
    }
}
