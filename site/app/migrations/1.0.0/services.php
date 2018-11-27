<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ServicesMigration_100
 */
class ServicesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('services', [
                'columns' => [
                    new Column(
                        'serviceid',
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
                            'after' => 'serviceid'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'pricemin',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'description'
                        ]
                    ),
                    new Column(
                        'pricemax',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'pricemin'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'pricemax'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'deleted'
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
                        'datepublication',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'regionid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'datepublication'
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 200,
                            'after' => 'regionid'
                        ]
                    ),
                    new Column(
                        'numberofdisplay',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'rating',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "2.5",
                            'size' => 24,
                            'after' => 'numberofdisplay'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('service_pkey', ['serviceid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_services_regions_regionId',
                        [
                            'referencedTable' => 'regions',
                            'referencedSchema' => 'public',
                            'columns' => ['regionid'],
                            'referencedColumns' => ['regionid'],
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
