<?php

use Phinx\Migration\AbstractMigration;

class CreateTransactionsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table( 'transactions', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $table->addColumn('description', 'string', array( 'limit' => 64 ));
        $table->addColumn('amount', 'decimal');
        $table->addColumn('purchased_at', 'date');
        $table->addColumn('category_id', 'integer');
        $table->addColumn('user_id', 'integer');
        $table->addColumn('fund_id', 'integer');

        // timestamps
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', array( 'null' => true ));
        $table->addColumn('deleted_at', 'datetime', array( 'null' => true ));

        $table->addIndex('user_id');
        $table->addIndex('fund_id');

        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable( 'transactions' );
    }
}
