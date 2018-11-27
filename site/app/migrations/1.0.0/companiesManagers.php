<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CompaniesmanagersMigration_100
 */
class CompaniesmanagersMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('companiesManagers', [
                'columns' => [
                    new Column(
                        'companyid',
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
                            'after' => 'companyid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('companiesManagers_pkey', ['companyid', 'userid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_companiesManagers_companies_companyId',
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
                        'foreignkey_companiesManagers_users_userId',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'public',
                            'columns' => ['userid'],
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
