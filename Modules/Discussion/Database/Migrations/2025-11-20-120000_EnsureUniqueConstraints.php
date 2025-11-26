<?php

namespace Modules\Discussion\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureUniqueConstraints extends Migration
{
    public function up()
    {
        // Ensure UNIQUE constraint on comment_likes table
        // One user/guest can only like a comment once
        if ($this->db->tableExists('comment_likes')) {
            // Check if unique constraint exists
            $indexes = $this->db->getIndexData('comment_likes');
            $hasUniqueUser = false;
            $hasUniqueGuest = false;
            
            foreach ($indexes as $index) {
                if ($index->name === 'comment_likes_comment_id_user_id' || 
                    (isset($index->fields) && in_array('comment_id', $index->fields) && in_array('user_id', $index->fields) && $index->type === 'UNIQUE')) {
                    $hasUniqueUser = true;
                }
                if ($index->name === 'comment_likes_comment_id_guest_ip' || 
                    (isset($index->fields) && in_array('comment_id', $index->fields) && in_array('guest_ip', $index->fields) && $index->type === 'UNIQUE')) {
                    $hasUniqueGuest = true;
                }
            }
            
            // Add unique constraint if not exists (using raw SQL for better control)
            if (!$hasUniqueUser) {
                // Try to add unique constraint for user_id (where user_id IS NOT NULL)
                // Note: MySQL doesn't support partial unique indexes directly, so we use composite
                $this->db->query("
                    ALTER TABLE comment_likes 
                    ADD UNIQUE KEY unique_comment_user (comment_id, user_id)
                ");
            }
            
            if (!$hasUniqueGuest) {
                // Try to add unique constraint for guest_ip (where guest_ip IS NOT NULL)
                $this->db->query("
                    ALTER TABLE comment_likes 
                    ADD UNIQUE KEY unique_comment_guest (comment_id, guest_ip)
                ");
            }
        }
        
        // Ensure UNIQUE constraint on comment_amins table
        if ($this->db->tableExists('comment_amins')) {
            $indexes = $this->db->getIndexData('comment_amins');
            $hasUniqueUser = false;
            $hasUniqueGuest = false;
            
            foreach ($indexes as $index) {
                if ($index->name === 'comment_amins_comment_id_user_id' || 
                    (isset($index->fields) && in_array('comment_id', $index->fields) && in_array('user_id', $index->fields) && $index->type === 'UNIQUE')) {
                    $hasUniqueUser = true;
                }
                if ($index->name === 'comment_amins_comment_id_guest_ip' || 
                    (isset($index->fields) && in_array('comment_id', $index->fields) && in_array('guest_ip', $index->fields) && $index->type === 'UNIQUE')) {
                    $hasUniqueGuest = true;
                }
            }
            
            if (!$hasUniqueUser) {
                $this->db->query("
                    ALTER TABLE comment_amins 
                    ADD UNIQUE KEY unique_comment_user (comment_id, user_id)
                ");
            }
            
            if (!$hasUniqueGuest) {
                $this->db->query("
                    ALTER TABLE comment_amins 
                    ADD UNIQUE KEY unique_comment_guest (comment_id, guest_ip)
                ");
            }
        }
    }

    public function down()
    {
        // Remove unique constraints if needed
        if ($this->db->tableExists('comment_likes')) {
            try {
                $this->db->query("ALTER TABLE comment_likes DROP INDEX unique_comment_user");
            } catch (\Exception $e) {
                // Index might not exist
            }
            try {
                $this->db->query("ALTER TABLE comment_likes DROP INDEX unique_comment_guest");
            } catch (\Exception $e) {
                // Index might not exist
            }
        }
        
        if ($this->db->tableExists('comment_amins')) {
            try {
                $this->db->query("ALTER TABLE comment_amins DROP INDEX unique_comment_user");
            } catch (\Exception $e) {
                // Index might not exist
            }
            try {
                $this->db->query("ALTER TABLE comment_amins DROP INDEX unique_comment_guest");
            } catch (\Exception $e) {
                // Index might not exist
            }
        }
    }
}

