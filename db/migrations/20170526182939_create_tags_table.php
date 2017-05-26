<?php

use Phinx\Migration\AbstractMigration;

class CreateTagsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table( 'tags', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $table->addColumn('name', 'string', array( 'limit' => 64 ));
        $table->addColumn('user_id', 'integer');

        // timestamps
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', array( 'null' => true ));
        $table->addColumn('deleted_at', 'datetime', array( 'null' => true ));

        $table->addIndex('user_id');

        $table->save();


        // create pivot table
        $pivotTable = $this->table( 'tag_transaction', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $pivotTable->addColumn('tag_id', 'integer');
        $pivotTable->addColumn('transaction_id', 'integer');

        // timestamps
        $pivotTable->addColumn('created_at', 'datetime');
        $pivotTable->addColumn('updated_at', 'datetime', array( 'null' => true ));
        $pivotTable->addColumn('deleted_at', 'datetime', array( 'null' => true ));

        $pivotTable->addIndex(array('tag_id', 'transaction_id'));
        $pivotTable->addForeignKey('tag_id', 'tags', 'id', array('delete'=> 'CASCADE', 'update'=> 'NO_ACTION'));
        $pivotTable->addForeignKey('transaction_id', 'transactions', 'id', array('delete'=> 'CASCADE', 'update'=> 'NO_ACTION'));

        $pivotTable->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable( 'tags' );
        $this->dropTable( 'tag_transaction' );
    }
}
