<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApiTokensFixture
 */
class ApiTokensFixture extends TestFixture
{
    /**
     * Plain-text tokens corresponding to the fixture rows. Tests use these to
     * build the Authorization header; only the hashes are stored.
     */
    const ADMIN_TOKEN = 'test-admin-token-plain';
    const LIMITED_TOKEN = 'test-limited-token-plain';
    const REVOKED_TOKEN = 'test-revoked-token-plain';
    const EXPIRED_TOKEN = 'test-expired-token-plain';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'token_hash' => ['type' => 'string', 'length' => 64, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'scopes' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created_by' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'last_used_at' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'expires_at' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'revoked' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'uq_api_tokens_token_hash' => ['type' => 'unique', 'columns' => ['token_hash'], 'length' => []],
        ],
        '_options' => ['engine' => 'InnoDB', 'collation' => 'utf8_general_ci']
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'test-admin-token',
                'token_hash' => hash('sha256', self::ADMIN_TOKEN),
                'scopes' => 'admin',
                'created_by' => 'tester',
                'last_used_at' => null,
                'expires_at' => null,
                'revoked' => false,
                'created' => '2026-10-03 19:00:00',
                'modified' => '2026-10-03 19:00:00',
            ],
            [
                'id' => 2,
                'name' => 'test-limited-token',
                'token_hash' => hash('sha256', self::LIMITED_TOKEN),
                'scopes' => 'events:read',
                'created_by' => 'tester',
                'last_used_at' => null,
                'expires_at' => null,
                'revoked' => false,
                'created' => '2026-10-03 19:00:00',
                'modified' => '2026-10-03 19:00:00',
            ],
            [
                'id' => 3,
                'name' => 'test-revoked-token',
                'token_hash' => hash('sha256', self::REVOKED_TOKEN),
                'scopes' => 'admin',
                'created_by' => 'tester',
                'last_used_at' => null,
                'expires_at' => null,
                'revoked' => true,
                'created' => '2026-10-03 19:00:00',
                'modified' => '2026-10-03 19:00:00',
            ],
            [
                'id' => 4,
                'name' => 'test-expired-token',
                'token_hash' => hash('sha256', self::EXPIRED_TOKEN),
                'scopes' => 'admin',
                'created_by' => 'tester',
                'last_used_at' => null,
                'expires_at' => '2020-01-01 00:00:00',
                'revoked' => false,
                'created' => '2026-10-03 19:00:00',
                'modified' => '2026-10-03 19:00:00',
            ],
        ];
        parent::init();
    }
}
