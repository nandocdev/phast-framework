<?php
/**
 * @package     phast/migrations
 * @file        001_create_users_table
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Create users table migration
 */

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration {
   /**
    * Change Method.
    *
    * Write your reversible migrations using this method.
    *
    * More information on writing migrations is available here:
    * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    *
    * The following commands can be used in this method and Phinx will
    * automatically reverse them when rolling back:
    *
    *    createTable
    *    renameTable
    *    addColumn
    *    addCustomColumn
    *    renameColumn
    *    addIndex
    *    addForeignKey
    *
    * Any other destructive changes will result in an error when trying to
    * rollback the migration.
    *
    * Remember to call "create()" or "update()" and NOT "save()" when working
    * with the Table class.
    */
   public function change() {
      $users = $this->table('users');
      $users->addColumn('name', 'string', ['limit' => 100])
         ->addColumn('email', 'string', ['limit' => 255])
         ->addColumn('password', 'string', ['limit' => 255])
         ->addColumn('email_verified_at', 'timestamp', ['null' => true])
         ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
         ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
         ->addIndex(['email'], ['unique' => true])
         ->create();
   }
}
