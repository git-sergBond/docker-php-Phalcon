<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ServicespointsMigration_100
 */
class ServicespointsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('servicesPoints', [
                'columns' => [
                    new Column(
                        'serviceid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'pointid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'serviceid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('servicesPoints_pkey', ['serviceid', 'pointid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_servicesPoints_tradePoints_pointId',
                        [
                            'referencedTable' => 'tradePoints',
                            'referencedSchema' => 'public',
                            'columns' => ['pointid'],
                            'referencedColumns' => ['pointid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'foreignkey_servicesPoints_services_serviceId',
                        [
                            'referencedTable' => 'services',
                            'referencedSchema' => 'public',
                            'columns' => ['serviceid'],
                            'referencedColumns' => ['serviceid'],
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
