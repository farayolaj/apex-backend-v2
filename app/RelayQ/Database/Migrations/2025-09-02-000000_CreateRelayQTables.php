<?php

namespace Alatise\RelayQ\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRelayQTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'queue' => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'default'],
            'job_class' => ['type' => 'VARCHAR', 'constraint' => 255],
            'payload' => ['type' => 'TEXT', 'null' => true], // JSON as TEXT (portable)
            'attempts' => ['type' => 'INT', 'default' => 0],
            'max_attempts' => ['type' => 'INT', 'default' => 3],
            'available_at' => ['type' => 'TIMESTAMP'],
            'reserved_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'last_error' => ['type' => 'TEXT', 'null' => true],
            'unique_key' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'unique_until' => ['type' => 'TIMESTAMP', 'null' => true],
            'last_handoff_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['queue', 'available_at']);
        $this->forge->addUniqueKey('unique_key');
        $this->forge->createTable('relayq_jobs', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'job_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'job_class' => ['type' => 'VARCHAR', 'constraint' => 255],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'error' => ['type' => 'TEXT', 'null' => true],
            'failed_at' => ['type' => 'TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('relayq_failed', true);
    }

    public function down()
    {
        $this->forge->dropTable('relayq_failed', true);
        $this->forge->dropTable('relayq_jobs', true);
    }
}
