<?php

namespace Modules\Discussion\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateLikesAminsToGuestToken extends Migration
{
    public function up()
    {
        // Update comment_likes table: replace guest_ip with guest_id
        if ($this->db->tableExists('comment_likes')) {
            // Check if guest_id column already exists
            $fields = $this->db->getFieldData('comment_likes');
            $hasGuestId = false;
            foreach ($fields as $field) {
                if ($field->name === 'guest_id') {
                    $hasGuestId = true;
                    break;
                }
            }
            
            if (!$hasGuestId) {
                // Add guest_id column
                $this->forge->addColumn('comment_likes', [
                    'guest_id' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'after' => 'user_id',
                        'comment' => 'Guest token untuk identifikasi guest (UUID)',
                    ],
                ]);
                
                // Migrate existing guest_ip data to guest_id (generate tokens for existing IPs)
                // Note: This is a one-time migration, existing IPs will get new tokens
                $this->db->query("
                    UPDATE comment_likes 
                    SET guest_id = CONCAT('migrated_', MD5(CONCAT(guest_ip, id)))
                    WHERE guest_ip IS NOT NULL AND guest_id IS NULL
                ");
                
                // Drop old unique constraint on guest_ip if exists
                try {
                    $this->db->query("ALTER TABLE comment_likes DROP INDEX unique_comment_guest");
                } catch (\Exception $e) {
                    // Index might not exist
                }
                
                // Add new unique constraint on guest_id
                $this->db->query("
                    ALTER TABLE comment_likes 
                    ADD UNIQUE KEY unique_comment_guest_id (comment_id, guest_id)
                ");
                
                // Drop guest_ip column after migration (optional, keep for now for safety)
                // $this->forge->dropColumn('comment_likes', 'guest_ip');
            }
        }
        
        // Update comment_amins table: replace guest_ip with guest_id
        if ($this->db->tableExists('comment_amins')) {
            $fields = $this->db->getFieldData('comment_amins');
            $hasGuestId = false;
            foreach ($fields as $field) {
                if ($field->name === 'guest_id') {
                    $hasGuestId = true;
                    break;
                }
            }
            
            if (!$hasGuestId) {
                // Add guest_id column
                $this->forge->addColumn('comment_amins', [
                    'guest_id' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'after' => 'user_id',
                        'comment' => 'Guest token untuk identifikasi guest (UUID)',
                    ],
                ]);
                
                // Migrate existing guest_ip data to guest_id
                $this->db->query("
                    UPDATE comment_amins 
                    SET guest_id = CONCAT('migrated_', MD5(CONCAT(guest_ip, id)))
                    WHERE guest_ip IS NOT NULL AND guest_id IS NULL
                ");
                
                // Drop old unique constraint on guest_ip if exists
                try {
                    $this->db->query("ALTER TABLE comment_amins DROP INDEX unique_comment_guest");
                } catch (\Exception $e) {
                    // Index might not exist
                }
                
                // Add new unique constraint on guest_id
                $this->db->query("
                    ALTER TABLE comment_amins 
                    ADD UNIQUE KEY unique_comment_guest_id (comment_id, guest_id)
                ");
                
                // Drop guest_ip column after migration (optional, keep for now for safety)
                // $this->forge->dropColumn('comment_amins', 'guest_ip');
            }
        }
    }

    public function down()
    {
        // Revert changes if needed
        if ($this->db->tableExists('comment_likes')) {
            try {
                $this->db->query("ALTER TABLE comment_likes DROP INDEX unique_comment_guest_id");
            } catch (\Exception $e) {
                // Index might not exist
            }
            try {
                $this->forge->dropColumn('comment_likes', 'guest_id');
            } catch (\Exception $e) {
                // Column might not exist
            }
        }
        
        if ($this->db->tableExists('comment_amins')) {
            try {
                $this->db->query("ALTER TABLE comment_amins DROP INDEX unique_comment_guest_id");
            } catch (\Exception $e) {
                // Index might not exist
            }
            try {
                $this->forge->dropColumn('comment_amins', 'guest_id');
            } catch (\Exception $e) {
                // Column might not exist
            }
        }
    }
}

