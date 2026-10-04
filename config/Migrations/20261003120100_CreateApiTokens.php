<?php
use Migrations\AbstractMigration;

class CreateApiTokens extends AbstractMigration
{
    /**
     * Create the api_tokens table.
     *
     * @return void
     */
    public function up()
    {
        $this->table('api_tokens')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('token_hash', 'char', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('scopes', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created_by', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('last_used_at', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('expires_at', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('revoked', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['token_hash'], [
                'unique' => true,
                'name' => 'uq_api_tokens_token_hash',
            ])
            ->create();
    }

    /**
     * Drop the api_tokens table.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable('api_tokens');
    }
}
