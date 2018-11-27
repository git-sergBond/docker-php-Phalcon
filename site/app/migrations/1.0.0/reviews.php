<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ReviewsMigration_100
 */
class ReviewsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('reviews', [
                'columns' => [
                    new Column(
                        'reviewid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'textreview',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'reviewid'
                        ]
                    ),
                    new Column(
                        'reviewdate',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'textreview'
                        ]
                    ),
                    new Column(
                        'rating',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'reviewdate'
                        ]
                    ),
                    new Column(
                        'fake',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'rating'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'fake'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'after' => 'deleted'
                        ]
                    ),
                    new Column(
                        'binderid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'executor',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'notNull' => true,
                            'after' => 'binderid'
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'executor'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'objectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'objecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'objectid'
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'objecttype'
                        ]
                    ),
                    new Column(
                        'bindertype',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 1,
                            'after' => 'userid'
                        ]
                    ),
                    new Column(
                        'fakename',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 180,
                            'after' => 'bindertype'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('reviews_pkey', ['reviewid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_reviews_users_userid',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'public',
                            'columns' => ['userid'],
                            'referencedColumns' => ['userid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'SET NULL'
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
