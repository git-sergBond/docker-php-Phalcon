<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UsersMigration_100
 */
class UsersMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('users', [
                'columns' => [
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 30,
                            'after' => 'userid'
                        ]
                    ),
                    new Column(
                        'password',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 64,
                            'after' => 'email'
                        ]
                    ),
                    new Column(
                        'role',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 27,
                            'after' => 'password'
                        ]
                    ),
                    new Column(
                        'fake',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'after' => 'role'
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
                        'phoneid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'after' => 'deleted'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'after' => 'phoneid'
                        ]
                    ),
                    new Column(
                        'issocial',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'notNull' => true,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'activated',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'notNull' => true,
                            'after' => 'issocial'
                        ]
                    ),
                    new Column(
                        'dateregistration',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'size' => 1,
                            'after' => 'activated'
                        ]
                    ),
                    new Column(
                        'activationcode',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 200,
                            'after' => 'dateregistration'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('users_pkey', ['userid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_users_phones',
                        [
                            'referencedTable' => 'phones',
                            'referencedSchema' => 'public',
                            'columns' => ['phoneid'],
                            'referencedColumns' => ['phoneid'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'RESTRICT'
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
