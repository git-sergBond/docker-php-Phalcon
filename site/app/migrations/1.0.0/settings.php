<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SettingsMigration_100
 */
class SettingsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('settings', [
                'columns' => [
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'notificationemail',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'notNull' => true,
                            'after' => 'userid'
                        ]
                    ),
                    new Column(
                        'notificationpush',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'notNull' => true,
                            'after' => 'notificationemail'
                        ]
                    ),
                    new Column(
                        'notificationsms',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'notNull' => true,
                            'after' => 'notificationpush'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('settings_pkey', ['userid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_settings_users_userId',
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
