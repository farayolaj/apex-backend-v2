<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MoveCourseGuideIdToCourseSettings extends AbstractMigration
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
        // First, migrate existing course_guide_id data from courses to course_settings
        $courses = $this->fetchAll("SELECT id, course_guide_id FROM courses WHERE course_guide_id IS NOT NULL AND course_guide_id != ''");

        foreach ($courses as $course) {
            // We'll create a course setting for the most recent session 
            // You might need to adjust this logic based on your business needs
            $recentSession = $this->fetchRow("SELECT id FROM sessions ORDER BY id DESC LIMIT 1");

            if ($recentSession) {
                $this->execute("
                    INSERT INTO course_settings (course_id, session_id, course_guide_id, created_at, updated_at)
                    VALUES ({$course['id']}, {$recentSession['id']}, '{$course['course_guide_id']}', NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                    course_guide_id = '{$course['course_guide_id']}',
                    updated_at = NOW()
                ");
            }
        }

        // Remove course_guide_id column from courses table
        $table = $this->table('courses');
        if ($table->hasColumn('course_guide_id')) {
            $table->removeColumn('course_guide_id')->update();
        }
    }

    public function down(): void
    {
        // Add course_guide_id column back to courses table
        $table = $this->table('courses');
        $table->addColumn('course_guide_id', 'string', [
            'limit' => 255,
            'null' => true,
            'comment' => 'Google Drive file ID for course guide document'
        ])->update();

        // Migrate data back from course_settings to courses
        $settings = $this->fetchAll("SELECT course_id, course_guide_id FROM course_settings WHERE course_guide_id IS NOT NULL AND course_guide_id != ''");

        foreach ($settings as $setting) {
            $this->execute("
                UPDATE courses 
                SET course_guide_id = '{$setting['course_guide_id']}' 
                WHERE id = {$setting['course_id']}
            ");
        }

        // Remove course_guide_id from course_settings (this will be handled by the previous migration rollback)
    }
}
