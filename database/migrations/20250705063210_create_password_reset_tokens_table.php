<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetTokensTable extends AbstractMigration {
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
     * -  **password_reset_tokens**
     *   -  **Depende de:** (Ninguna - relación lógica con `users`)
     *   -  **Tablas que dependen de esta:** (Ninguna)
     *   -  **Campos:**
     *      -  `email` varchar(100)
     *      -  `token` varchar(255)
     *      -  `created_at` timestamp
     */
    public function change(): void {
        $table = $this->table("password_reset_tokens");
        $table->addColumn("email", "string", ["limit" => 100])
            ->addColumn("token", "string", ["limit" => 255])
            ->addColumn("created_at", "timestamp", ["default" => "CURRENT_TIMESTAMP"])
            ->create();
    }
}
