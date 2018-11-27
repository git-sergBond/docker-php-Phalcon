<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CategoriesMigration_100
 */
class CategoriesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('categories', [
                'columns' => [
                    new Column(
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'categoryname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'categoryid'
                        ]
                    ),
                    new Column(
                        'parentid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'categoryname'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'parentid'
                        ]
                    ),
                    new Column(
                        'img',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 260,
                            'after' => 'description'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('categories_categoryName_idx', ['categoryname'], null),
                    new Index('categories_pkey', ['categoryid'], null)
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
