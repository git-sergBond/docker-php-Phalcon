<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class FavoriteusersMigration_100
 */
class FavoriteusersMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('favoriteUsers', [
                'columns' => [
                    new Column(
                        'usersubject',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'userobject',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'usersubject'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('favoriteUsers_pkey', ['usersubject', 'userobject'], null),
                    new Index('favoriteUsers_userObject_idx', ['userobject'], null)
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
