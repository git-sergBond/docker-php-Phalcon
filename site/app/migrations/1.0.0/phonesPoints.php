<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class PhonespointsMigration_100
 */
class PhonespointsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('phonesPoints', [
                'columns' => [
                    new Column(
                        'phoneid',
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
                            'after' => 'phoneid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('phonesPoints_pkey', ['phoneid', 'pointid'], null),
                    new Index('phonesPoints_pointId_idx', ['pointid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_phonesPoints_phones_phoneId',
                        [
                            'referencedTable' => 'phones',
                            'referencedSchema' => 'public',
                            'columns' => ['phoneid'],
                            'referencedColumns' => ['phoneid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'RESTRICT'
                        ]
                    ),
                    new Reference(
                        'foreignkey_phonesPoints_tradePoints_pointId',
                        [
                            'referencedTable' => 'tradePoints',
                            'referencedSchema' => 'public',
                            'columns' => ['pointid'],
                            'referencedColumns' => ['pointid'],
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
