<?php

namespace Modules\Report\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReports extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL untuk laporan global/platform',
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL untuk laporan semua campaign',
            ],
            'report_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'financial, transparency, monthly, annual, campaign_summary',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Judul laporan',
            ],
            'period_start' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Periode awal',
            ],
            'period_end' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Periode akhir',
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Ringkasan laporan',
            ],
            'data' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: Data laporan detail',
            ],
            'total_donations' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Total donasi masuk',
            ],
            'total_withdrawals' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Total penyaluran',
            ],
            'total_campaigns' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Jumlah campaign',
            ],
            'total_donors' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Jumlah donatur',
            ],
            'is_public' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Public visibility',
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu publish',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('report_type');
        $this->forge->addKey('is_public');
        $this->forge->addKey('published_at');
        $this->forge->addKey('created_at');

        $this->forge->createTable('reports');
    }

    public function down()
    {
        $this->forge->dropTable('reports');
    }
}

