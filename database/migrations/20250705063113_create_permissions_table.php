<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePermissionsTable extends AbstractMigration {
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

    /**
     * -  **permissions**
     *   -  **Depende de:** (Ninguna)
     *   -  **Tablas que dependen de esta:** `user_group_permissions`
     *   -  **Campos:**
     *      -  `id` int(11)
     *      -  `name` varchar(100)
     *      -  `description` text
     *      -  `module` varchar(50)
     *      -  `created_at` datetime
     *      -  `updated_at` datetime
     * @return void
     */
    public function change(): void {
        $table = $this->table("permissions");
        $table->addColumn("name", "string", ["limit" => 100])
            ->addColumn("description", "text")
            ->addColumn("module", "string", ["limit" => 50])
            ->addColumn("created_at", "datetime", ["default" => "CURRENT_TIMESTAMP"])
            ->addColumn("updated_at", "datetime", ["default" => "CURRENT_TIMESTAMP", "update" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
