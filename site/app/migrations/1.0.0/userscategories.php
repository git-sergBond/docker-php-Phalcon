<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserscategoriesMigration_100
 */
class UserscategoriesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('userscategories', [
                'columns' => [
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'userid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('userscategories_pkey', ['userid', 'categoryid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_userscategories_categories_categoryid',
                        [
                            'referencedTable' => 'categories',
                            'referencedSchema' => 'public',
                            'columns' => ['categoryid'],
                            'referencedColumns' => ['categoryid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'foreignkey_userscategories_users_userid',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'public',
                            'columns' => ['userid'],
                            'referencedColumns' => ['userid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'CASCADE'
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
