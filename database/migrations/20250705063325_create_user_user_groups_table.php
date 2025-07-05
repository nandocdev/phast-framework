<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserUserGroupsTable extends AbstractMigration {
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
     * -  **user_user_groups**
     *   -  **Depende de:** `users`, `user_groups`, `organizational_units` (tabla externa al módulo `Auth`)
     *   -  **Tablas que dependen de esta:** (Ninguna)
     *   -  **Campos:**
     *      -  `user_id` int(11)
     *      -  `user_group_id` int(11)
     *      -  `organizational_unit_id` int(11)
     *      -  `assigned_at` datetime
     */
    public function change(): void {
        $table = $this->table("user_user_groups");
        $table->addColumn("user_id", "integer", ["limit" => 11])
            ->addColumn("user_group_id", "integer", ["limit" => 11])
            ->addColumn("organizational_unit_id", "integer", ["limit" => 11])
            ->addColumn("assigned_at", "datetime", ["default" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
