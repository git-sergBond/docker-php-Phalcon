<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CompaniescategoriesMigration_100
 */
class CompaniescategoriesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('companiesCategories', [
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
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'after' => 'companyid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('companiesCategories_categoryId_idx', ['categoryid'], null),
                    new Index('companiesCategories_pkey', ['companyid', 'categoryid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_companiesCategories_categories_categoryId',
                        [
                            'referencedTable' => 'categories',
                            'referencedSchema' => 'public',
                            'columns' => ['categoryid'],
                            'referencedColumns' => ['categoryid'],
                            'onUpdate' => 'CASCADE',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'foreignkey_companiesCategories_companies_companyId',
                        [
                            'referencedTable' => 'companies',
                            'referencedSchema' => 'public',
                            'columns' => ['companyid'],
                            'referencedColumns' => ['companyid'],
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
