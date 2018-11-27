<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class LogsMigration_100
 */
class LogsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('logs', [
                'columns' => [
                    new Column(
                        'logid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'logid'
                        ]
                    ),
                    new Column(
                        'controller',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 60,
                            'after' => 'userid'
                        ]
                    ),
                    new Column(
                        'action',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 60,
                            'after' => 'controller'
                        ]
                    ),
                    new Column(
                        'date',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'size' => 1,
                            'after' => 'action'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('logs_pkey', ['logid'], null),
                    new Index('logs_userId_idx', ['userid'], null)
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
