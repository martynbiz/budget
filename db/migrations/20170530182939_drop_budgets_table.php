<?php

use Phinx\Migration\AbstractMigration;

class DropBudgetsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('budgets');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table( 'budgets', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci'
        ));

        $table->addColumn('amount', 'integer');
        $table->addColumn('category_id', 'integer');
        $table->addColumn('fund_id', 'integer');

        // timestamps
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', array( 'null' => true ));
        $table->addColumn('deleted_at', 'datetime', array( 'null' => true ));

        $table->addIndex('category_id');
        $table->addIndex('fund_id');

        $table->save();
    }
}
