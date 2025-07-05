<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserGroupsTable extends AbstractMigration {
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
     * -  **user_groups**
     *   -  **Depende de:** (Ninguna)
     *   -  **Tablas que dependen de esta:** `user_group_permissions`, `user_user_groups`
     *   -  **Campos:**
     *      -  `id` int(11)
     *      -  `name` varchar(100)
     *      -  `description` text
     *      -  `created_at` datetime
     *      -  `updated_at` datetime
     */
    public function change(): void {
        $table = $this->table("user_groups");
        $table->addColumn("name", "string", ["limit" => 100])
            ->addColumn("description", "text")
            ->addColumn("created_at", "datetime", ["default" => "CURRENT_TIMESTAMP"])
            ->addColumn("updated_at", "datetime", ["default" => "CURRENT_TIMESTAMP", "update" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
