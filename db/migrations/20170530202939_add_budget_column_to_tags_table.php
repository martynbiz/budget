<?php

use Phinx\Migration\AbstractMigration;

class AddBudgetColumnToTagsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('tags');
        $table->addColumn('budget', 'decimal', array('precision' => 10, 'scale' => 2))
              ->update();
    }
}
