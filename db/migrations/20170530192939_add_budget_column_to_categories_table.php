<?php

use Phinx\Migration\AbstractMigration;

class AddBudgetColumnToCategoriesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('categories');
        $table->addColumn('budget', 'decimal', array('precision' => 10, 'scale' => 2))
              ->update();
    }
}
