<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class PhonescompaniesMigration_100
 */
class PhonescompaniesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('phonesCompanies', [
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
                        'companyid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'phoneid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('phonesCompanies_companyId_idx', ['companyid'], null),
                    new Index('phonesCompanies_pkey', ['phoneid', 'companyid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_phonesCompanies_companies_companyId',
                        [
                            'referencedTable' => 'companies',
                            'referencedSchema' => 'public',
                            'columns' => ['companyid'],
                            'referencedColumns' => ['companyid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'foreignkey_phonesCompanies_phones_phoneId',
                        [
                            'referencedTable' => 'phones',
                            'referencedSchema' => 'public',
                            'columns' => ['phoneid'],
                            'referencedColumns' => ['phoneid'],
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
