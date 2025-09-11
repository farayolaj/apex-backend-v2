<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateRecordingColumnsInWebinarsTable extends AbstractMigration
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
        $table = $this->table('webinars');

        $table->addColumn('recordings', 'json', [
            'default' => '[]',
            'after' => 'end_time',
            'comment' => 'Stores recording metadata as JSON'
        ])->update();

        $this->execute('UPDATE webinars SET recordings = JSON_ARRAY(JSON_OBJECT(
            "id", recording_id,
            "url", recording_url,
            "date", now()
        )) WHERE recording_id IS NOT NULL AND recording_url IS NOT NULL');

        $table->removeColumn('recording_id')
            ->removeColumn('recording_url')
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('webinars');

        $table
            ->addColumn('recording_id', 'string', [
                'after' => 'end_time',
                'limit' => 128,
            ])
            ->addColumn('recording_url', 'string', [
                'limit' => 2048,
                'after' => 'recording_id',
            ])
            ->update();

        $this->execute("UPDATE webinars SET
            recording_id = recordings->>'$[0].id',
            recording_url = recordings->>'$[0].url'
            WHERE recordings->>'$[0].id' IS NOT NULL
            AND recordings->>'$[0].url' IS NOT NULL");

        $table->removeColumn('recordings')
            ->update();
    }
}
