<?php

use Phinx\Migration\AbstractMigration;

class CreateApiTokensTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table( 'api_tokens', array(
            'engine' => 'InnoDB',
            'collation' => 'utf8_unicode_ci',
        ));

        $table->addColumn('value', 'string', array( 'limit' => 64));
        $table->addColumn('user_id', 'integer');
        $table->addColumn('expires_at', 'datetime');

        $table->addForeignKey('user_id', 'users', 'id', array('delete'=> 'CASCADE', 'update'=> 'NO_ACTION'));

        // timestamps
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', array( 'null' => true));
        $table->addColumn('deleted_at', 'datetime', array( 'null' => true));

        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable( 'api_tokens' );
    }
}
