<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableAuditLogs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('audit_logs');
            $table->addColumn('host', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('url', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('user_agent', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('ip_address', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->create();
    }
}
