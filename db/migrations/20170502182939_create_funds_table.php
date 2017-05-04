<?php

use Phinx\Migration\AbstractMigration;

class CreateFundsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table( 'funds', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $table->addColumn('name', 'string', array( 'limit' => 64 ));
        $table->addColumn('amount', 'decimal');
        $table->addColumn('currency_id', 'integer');
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
        $this->dropTable( 'funds' );
    }
}
