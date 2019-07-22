<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    use \Crud\Controller\ControllerTrait;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $auth = [
            'ActiveDirectoryAuthenticate.Adldap' => [
                'config' => [
                    'account_suffix' => Configure::read('ActiveDirectory.account_suffix'),
                    'base_dn' => Configure::read('ActiveDirectory.base_dn'),
                    'domain_controllers' => Configure::read('ActiveDirectory.domain_controllers')
                ],
                'select' => ['displayName', 'samaccountname', 'telephonenumber', 'mail']
            ]
        ];

        // If Mock exists in config file then use that instead of real AD auth
        if (Configure::check("MockActiveDirectory")) {
            $auth = [
                'ActiveDirectoryAuthenticateMock.AdldapMock' => Configure::read("MockActiveDirectory")
            ];
        }

        $this->loadComponent('Auth', [
            'authenticate' => $auth,
            'authorize' => ['Controller'],
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login'
            ],
            'loginRedirect' => [
                'controller' => 'Events',
                'action' => 'index'
            ],
            'logoutRedirect' => [
                'controller' => 'Events',
                'action' => 'index'
            ]
        ]);
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete'
            ]
        ]);
        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Security');

        // Disables CRUD's default setFlash helper
        $this->getEventManager()->on('Crud.setFlash', function (Event $event) {
            $event->stopPropagation();
        });

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
    }

    public function customLog($description) {
        // log this
        $this->logs = TableRegistry::getTableLocator()->get('Logs');
        $log = $this->logs->newEntity();
        $log->description   = $description;
        $log->user          = $this->Auth->user()['samaccountname'];
        $log->date_time     = date('Y-m-d H:i:s');
        $log->ip_address    = $_SERVER['REMOTE_ADDR'];
        $log->url           = $_SERVER['REQUEST_URI'];
        $log->controller    = $this->request->getParam('controller');
        $log->action        = $this->request->getParam('action');

        $this->logs->save($log);
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $this->customLog('Viewed ' . $_SERVER[ 'REQUEST_URI' ]);

        $isAuthorized = [
            'canAddEvents' => 0,
            'canManageCategories' => 0,
            'canManageCommittees' => 0,
            'canManageConfigs' => 0,
            'canManageEvents' => 0,
            'canManagePreqequisites' => 0,
            'canManageRooms' => 0,
            'canManageTools' => 0,
            'canManageHonoraria' => 0,
            'canManageW9s' => 0,
            'canDisableHonoraria' => 0,
            'canManageFinanceReports' => 0
        ];

        $hasMenu = ['hasAdminMenu' => 0, 'hasFinancialMenu' => 0];

        if ($this->Auth->user()) {
            $authorizations = [
                'Members' => [
                    'canAddEvents',
                    'canManageOwnEvents'
                ],
                'Calendar Admins' => [
                    'canManageCategories',
                    'canManageCommittees',
                    'canManageConfigs',
                    'canManageContacts',
                    'canManageEvents',
                    'canManagePreqequisites',
                    'canManageRooms',
                    'canManageTools'
                ],
                'Honorarium Admins' => [
                    'canManageHonoraria'
                ],
                'Financial Reporting' => [
                    'canManageW9s',
                    'canManageFinanceReports',
                    'canExportHonoraria'
                ],
    'Calendar Super Admins' => [
        'canDisableHonoraria'
    ],
            ];

            foreach ($authorizations as $group => $authorizedActions) {
                if ($this->inAdminstrativeGroup($this->Auth->user(), $group)) {
                    $allow = true;
                } else {
                    $allow = false;
                }

                foreach ($authorizedActions as $authorizedAction) {
                    $isAuthorized[$authorizedAction] = $allow;
                }
            }
        }

        if ($this->inAdminstrativeGroup($this->Auth->user(), 'Calendar Admins')) {
            $hasMenu['hasAdminMenu'] = 1;
        }

        if ($this->inAdminstrativeGroup($this->Auth->user(), 'Calendar Super Admins')) {
            $hasMenu['hasCalendarAdminMenu'] = 1;
        }

        if ($this->inAdminstrativeGroup($this->Auth->user(), 'Financial Reporting')) {
            $hasMenu['hasFinancialMenu'] = 1;
        }

        $this->set($isAuthorized);
        $this->set($hasMenu);
        $this->set('isDevelopment', Configure::read("isDevelopment"));
        $this->set('isMockAuth', Configure::check("MockActiveDirectory"));
        
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->getType(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

    /**
     * Check if the provided user is authorized for the request.
     *
     * General purpose authorization for calendar admins (AD: Calendar Admins). Not all controllers call
     * parent::isAuthorized. Controllers which have granular adminstrative permissions should not call the
     * app-level auth method so that only specific admins have access to their allowed actions.
     *
     * These include:
     *   - Honorarium Admins having access to honoraria rejections while Calendar Admins don't have this
     *     power innately.
     *   - Financial Reporting users are the only users which have access to W9 data and financial data
     *     exports.
     *
     * Most controllers will call this parent method. Call it unless you have a reason (such as those above)
     * not to.
     *
     * @param array|null $user The user to check the authorization of.
     * @return bool True if $user is authorized, otherwise false
     */
    public function isAuthorized($user = null)
    {
        return $this->inAdminstrativeGroup($user, 'Calendar Admins');
    }

    /**
     * Check if the provided user is a member of a specified group.
     *
     * @param array $user The user to check the authorization of.
     * @param string $group The AD group to check user membership against.
     * @return bool True if $user is authorized, otherwise false
     */
    public function inAdminstrativeGroup($user, $group)
    {
        if ($user && in_array($group, $user['groups'])) {
            return true;
        }

        return false;
    }
}
