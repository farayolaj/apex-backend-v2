<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangePhoneColumn extends AbstractMigration
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
    public function up(): void
    {
        $table = $this->table('applicants');
        $table->changeColumn('phone', 'string', ['limit' => 250])
        ->changeColumn('phone2', 'string', ['limit' => 250])
        ->save();

        $table = $this->table('students');
        $table->changeColumn('phone', 'string', ['limit' => 250])
        ->save();
    }

    public function down()
    {
        $table = $this->table('applicants');
        $table->changeColumn('phone', 'string', ['limit' => 20])
        ->changeColumn('phone2', 'string', ['limit' => 20])
        ->save();

        $table = $this->table('students');
        $table->changeColumn('phone', 'string', ['limit' => 15])
        ->save();
    }
}
