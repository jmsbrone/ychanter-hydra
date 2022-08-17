<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m220605_165528_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currentTimestamp = new Expression('CURRENT_TIMESTAMP');
        $this->createTable('{{%user}}', [
            'id' => 'serial PRIMARY KEY',
            'name' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultValue($currentTimestamp),
            'updated_at' => $this->timestamp()->notNull()->defaultValue($currentTimestamp),
        ]);
        $this->createTable('{{%login_psw_entity}}', [
            'id' => 'serial PRIMARY KEY',
            'created_at' => $this->timestamp()->notNull()->defaultValue($currentTimestamp),
            'updated_at' => $this->timestamp()->notNull()->defaultValue($currentTimestamp),
            'login' => $this->string(255)->notNull()->unique(),
            'passwordHash' => $this->string(255)->notNull()->unique(),
        ]);

        $this->addForeignKey(
            'fk_login_psw_entity_link',
            '{{%login_psw_entity}}', 'id',
            '{{%user}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%login_psw_entity}}');
        $this->dropTable('{{%user}}');
    }
}
