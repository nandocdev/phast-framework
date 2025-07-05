<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserGroupPermissionsTable extends AbstractMigration {
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
     * -  **user_group_permissions**
     *
     *   -  **Depende de:** `user_groups`, `permissions`
     *   -  **Tablas que dependen de esta:** (Ninguna)
     *   -  **Campos:**
     *      -  `user_group_id` int(11)
     *      -  `permission_id` int(11)
     *      -  `assigned_at` datetime
     *
     */
    public function change(): void {
        $table = $this->table("user_group_permissions");
        $table->addColumn("user_group_id", "integer", ["limit" => 11])
            ->addColumn("permission_id", "integer", ["limit" => 11])
            ->addColumn("assigned_at", "datetime", ["default" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
