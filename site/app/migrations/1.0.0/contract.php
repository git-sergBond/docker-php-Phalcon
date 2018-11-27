<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ContractMigration_100
 */
class ContractMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('contract', [
                'columns' => [
                    new Column(
                        'contractid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'contractnumber',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'contractid'
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'contractnumber'
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
                        'subjectidtwo',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'subjecttypetwo',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'subjectidtwo'
                        ]
                    ),
                    new Column(
                        'requisitesone',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 500,
                            'after' => 'subjecttypetwo'
                        ]
                    ),
                    new Column(
                        'requisitestwo',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 500,
                            'after' => 'requisitesone'
                        ]
                    ),
                    new Column(
                        'userorganizer',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'requisitestwo'
                        ]
                    ),
                    new Column(
                        'sum',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'userorganizer'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('contract_pkey', ['contractid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_contracts_users_userorganizer',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'public',
                            'columns' => ['userorganizer'],
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
