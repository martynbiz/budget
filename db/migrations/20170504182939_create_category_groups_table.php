<?php

use Phinx\Migration\AbstractMigration;

class CreateCategoryGroupsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table( 'category_groups', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $table->addColumn('name', 'string', array( 'limit' => 64 ));
        $table->addColumn('user_id', 'integer');

        // timestamps
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', array( 'null' => true ));
        $table->addColumn('deleted_at', 'datetime', array( 'null' => true ));

        $table->addForeignKey('user_id', 'users', 'id', array('delete'=> 'CASCADE', 'update'=> 'NO_ACTION'));
        $table->addIndex('user_id');

        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable( 'category_groups' );
    }
}
