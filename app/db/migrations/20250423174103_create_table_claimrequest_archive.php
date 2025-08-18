<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateTableClaimrequestArchive extends AbstractMigration
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
    private function duplicateUserRequestsTable()
    {
        $this->execute("DROP TABLE IF EXISTS course_request_claims_archive");
        $this->execute("
            CREATE TABLE course_request_claims_archive LIKE user_requests;
        ");
    }

    public function up(): void
    {

        $this->duplicateUserRequestsTable();
        $table = $this->table('course_request_claims_archive');
        $table->addColumn('claims_request_data', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM])
            ->addColumn('claims_request_data_items', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM])
            ->addColumn('user_request_id', 'integer', ['limit' => 11])
            ->update();
    }

    public function down()
    {
        // Drop the recreated table
        $this->execute("DROP TABLE IF EXISTS course_request_claims_archive");
    }
}
