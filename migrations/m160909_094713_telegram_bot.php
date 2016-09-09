<?php

use yii\db\Migration;

class m160909_094713_telegram_bot extends Migration
{
    public function up()
    {
        $this->execute(file_get_contents("vendor/longman/telegram-bot/structure.sql"));
    }

    public function down()
    {
        echo "m160909_094713_telegram_bot cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
