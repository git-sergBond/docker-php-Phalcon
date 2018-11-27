<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class TasksMigration_100
 */
class TasksMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('tasks', [
                'columns' => [
                    new Column(
                        'taskid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'taskid'
                        ]
                    ),
                    new Column(
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 100,
                            'after' => 'categoryid'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'deadline',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'size' => 1,
                            'after' => 'description'
                        ]
                    ),
                    new Column(
                        'price',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'deadline'
                        ]
                    ),
                    new Column(
                        'status',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'price'
                        ]
                    ),
                    new Column(
                        'polygon',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'status'
                        ]
                    ),
                    new Column(
                        'regionid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'polygon'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'regionid'
                        ]
                    ),
                    new Column(
                        'longitude',
                        [
                            'type' => Column::TYPE_DOUBLE,
                            'size' => 53,
                            'after' => 'deleted'
                        ]
                    ),
                    new Column(
                        'latitude',
                        [
                            'type' => Column::TYPE_DOUBLE,
                            'size' => 53,
                            'after' => 'longitude'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'latitude'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'datestart',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'dateend',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'datestart'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('tasks_categoryId_idx', ['categoryid'], null),
                    new Index('tasks_pkey', ['taskid'], null),
                    new Index('tasks_userId_idx', ['subjectid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_tasks_categories_categoryId',
                        [
                            'referencedTable' => 'categories',
                            'referencedSchema' => 'public',
                            'columns' => ['categoryid'],
                            'referencedColumns' => ['categoryid'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION'
                        ]
                    ),
                    new Reference(
                        'foreignkey_tasks_regions_regionId',
                        [
                            'referencedTable' => 'regions',
                            'referencedSchema' => 'public',
                            'columns' => ['regionid'],
                            'referencedColumns' => ['regionid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'SET NULL'
                        ]
                    ),
                    new Reference(
                        'foreignkey_tasks_statuses_status',
                        [
                            'referencedTable' => 'statuses',
                            'referencedSchema' => 'public',
                            'columns' => ['status'],
                            'referencedColumns' => ['statusid'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION'
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
