<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class FavoritecategoriesMigration_100
 */
class FavoritecategoriesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('favoriteCategories', [
                'columns' => [
                    new Column(
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'categoryid'
                        ]
                    ),
                    new Column(
                        'radius',
                        [
                            'type' => Column::TYPE_DOUBLE,
                            'size' => 53,
                            'after' => 'userid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('favoriteCategories_pkey', ['categoryid', 'userid'], null),
                    new Index('favoriteCategories_userId_idx', ['userid'], null)
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
