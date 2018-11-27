<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CompaniesMigration_100
 */
class CompaniesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('companies', [
                'columns' => [
                    new Column(
                        'companyid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 150,
                            'after' => 'companyid'
                        ]
                    ),
                    new Column(
                        'fullname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 180,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'tin',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 12,
                            'after' => 'fullname'
                        ]
                    ),
                    new Column(
                        'regionid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'tin'
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'regionid'
                        ]
                    ),
                    new Column(
                        'website',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 180,
                            'after' => 'userid'
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 180,
                            'after' => 'website'
                        ]
                    ),
                    new Column(
                        'ismaster',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'after' => 'email'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'ismaster'
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
                        'logotype',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 256,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'ratingexecutor',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "2.5",
                            'notNull' => true,
                            'size' => 24,
                            'after' => 'logotype'
                        ]
                    ),
                    new Column(
                        'ratingclient',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "2.5",
                            'notNull' => true,
                            'size' => 24,
                            'after' => 'ratingexecutor'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('companies_pkey', ['companyid'], null),
                    new Index('companies_regionId_idx', ['regionid'], null),
                    new Index('companies_userId_idx', ['userid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_companies_regions_regionId',
                        [
                            'referencedTable' => 'regions',
                            'referencedSchema' => 'public',
                            'columns' => ['regionid'],
                            'referencedColumns' => ['regionid'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION'
                        ]
                    ),
                    new Reference(
                        'foreignkey_companies_users_userId',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'public',
                            'columns' => ['userid'],
                            'referencedColumns' => ['userid'],
                            'onUpdate' => 'CASCADE',
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
