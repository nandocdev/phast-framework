<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration {
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
     * 
     * -  **users**
     *
     *  -  **Depende de:** `organizational_units` (tabla externa al módulo `Auth`)
     *  -  **Tablas que dependen de esta:** `user_user_groups`
     *  -  **Campos:**
     *      -  `id` int(11)
     *      -  `username` varchar(50)
     *      -  `password_hash` varchar(255)
     *      -  `first_name` varchar(100)
     *      -  `last_name` varchar(100)
     *      -  `cedula` varchar(20)
     *      -  `professor_code` varchar(20)
     *      -  `email` varchar(100)
     *      -  `office_phone` varchar(20)
     *      -  `personal_phone` varchar(20)
     *      -  `main_organizational_unit_id` int(11)
     *      -  `is_active` tinyint(1)
     *      -  `created_at` datetime
     *      -  `updated_at` datetime
     */
    public function change(): void {
        $table = $this->table("users");
        $table->addColumn("username", "string", ["limit" => 50])
            ->addColumn("password_hash", "string", ["limit" => 255])
            ->addColumn("first_name", "string", ["limit" => 100])
            ->addColumn("last_name", "string", ["limit" => 100])
            ->addColumn("cedula", "string", ["limit" => 20])
            ->addColumn("professor_code", "string", ["limit" => 20])
            ->addColumn("email", "string", ["limit" => 100])
            ->addColumn("office_phone", "string", ["limit" => 20])
            ->addColumn("personal_phone", "string", ["limit" => 20])
            ->addColumn("main_organizational_unit_id", "integer", ["limit" => 11])
            ->addColumn("is_active", "boolean", ["default" => true])
            ->addColumn("created_at", "datetime", ["default" => "CURRENT_TIMESTAMP"])
            ->addColumn("updated_at", "datetime", ["default" => "CURRENT_TIMESTAMP", "update" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
