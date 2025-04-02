<?php
namespace App\Auth;

use Cake\Core\Configure;
use Jumbojett\OpenIDConnectClient;

class OpenIDConnectService
{
    private $oidc;

    public function __construct()
    {
        $this->oidc = new OpenIDConnectClient(
            Configure::read('OIDC.url_authorize'),
            Configure::read('OIDC.client_id'),
            Configure::read('OIDC.client_secret')
        );
        $this->oidc->setRedirectURL(Configure::read('OIDC.redirect_uri'));
        $this->oidc->providerConfigParam(array('userinfo_endpoint'=>Configure::read('OIDC.url_resource_owner_details')));
    }

    /**
     * Authenticate with OIDC server
     */
    public function authenticate()
    {
        $this->oidc->addScope(['openid', 'profile', 'email']);
        $this->oidc->authenticate();
    }

    /**
     * OIDC Callback method, read userInfo returned by OIDC and populate auth user array
     * 
     * @return boolean true if authenticated
     */
    public function authenticateCallback($request)
    {
        $authenticated = $this->oidc->authenticate();
        if ($authenticated) {
            $this->storeTokenInSession($request);
        }
        return $authenticated;
    }

    private function storeTokenInSession($request)
    {
        $token = $this->oidc->getAccessToken();
        $request->getSession()->write('oidc_token', $token);
    }

    /**
     * Get user data from OIDC userInfo
     * 
     * @return array of user data that can be set to the Auth
     */
    public function getUserData()
    {
        $userInfo = $this->oidc->requestUserInfo();
        if (!$userInfo) {
            return null;
        }

        // If $userInfo->samaccountname is empty, the user is not authenticated
        if (empty($userInfo->preferred_username)) {
            return null;
        }

        // Map user data from userInfo (oidc) to user array
        $user = [];
        $user['ssologin'] = true;
        $user['ssoprofile'] = Configure::read('OIDC.url_authorize') . 'account';
        $user['samaccountname'] = $userInfo->preferred_username;
        $user['name'] = $userInfo->name;
        $user['email'] = $userInfo->email;
        $user['groups'] = $this->getGroupsFromUserInfo($userInfo->groups);

        return $user;
    }

    /**
     * Extract groups from OIDC userInfo formatted group string
     * 
     * @param $userInfo
     * @return array of user group name strings
     */
    public function getGroupsFromUserInfo($userInfoGroups)
    {
        // For each group in user info extract the group name from the string.
        // e.g.   openLDAP - "cn=Members,ou=Security,ou=Groups,dc=dms,dc=local"
        // ActiveDirectory - "/Members"
        $result = [];
        if (isset($userInfoGroups)) {
            foreach ($userInfoGroups as $group) {
                $matches = [];
                preg_match('/(cn=|\/)([^,]+)/', $group, $matches);
                if (isset($matches[2])) {
                    $result[] = $matches[2];
                }
            }
        }
    }

    /**
     * Update user groups from OIDC userInfo
     * 
     * @param $session Used to get the access token
     * @return array of user group name strings
     */
    public function updateGroups($session)
    {
        $this->oidc->setAccessToken($this->getTokenFromSession($session));
        $newUserInfo = $this->oidc->requestUserInfo("groups");
        return $this->getGroupsFromUserInfo($newUserInfo);
    }

    private function getTokenFromSession($session)
    {
        return $session->read('oidc_token');
    }
}