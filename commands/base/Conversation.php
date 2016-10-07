<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 14/09/16
 * Time: 10:02
 */

namespace app\commands\base;


use Longman\TelegramBot\ConversationDB;

class Conversation extends \Longman\TelegramBot\Conversation
{
    function __construct($user_id, $chat_id, $command)
    {
        $this->user_id = $user_id;
        $this->chat_id = $chat_id;
        $this->command = $command;

        $this->load();
    }

    function update()
    {
        if(!$this->exists())
        {
            $this->start();
        }

        return parent::update();
    }

    function start()
    {
        if (!$this->exists() && $this->command) {
            if (ConversationDB::insertConversation(
                $this->user_id,
                $this->chat_id,
                $this->command
            )) {
                $notes = $this->notes;
                $result =  $this->load();
                $this->notes = $notes;

                return $result;
            }
        }

        return false;
    }

}