<?php
use Migrations\AbstractMigration;

class UsersAdd extends AbstractMigration
{
    /**
     * up
     * @author ito
     */
    public function up()
    {
        $this->table('users')
            ->addColumn('username', 'text', [
                'comment' => 'ログインID',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('password', 'text', [
                'comment' => 'パスワード',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'text', [
                'comment' => '名前',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('email', 'text', [
                'comment' => 'メールアドレス',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('tmp_hash', 'text', [
                'comment' => 'ハッシュキー',
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('tmp_hash_issuance_time', 'timestamp', [
                'comment' => 'ハッシュキーの発行日時',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('active', 'boolean', [
                'comment' => '有効化',
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reseted', 'boolean', [
                'comment' => 'リセットされたパスワードか',
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'comment' => '登録日時',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'comment' => '更新日時',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'username',
                    'password',
                    'active',
                ]
            )
            ->create();
    }

    /**
     * down
     * @author ito
     */
    public function down()
    {
        $this->dropTable('users');
    }
}
