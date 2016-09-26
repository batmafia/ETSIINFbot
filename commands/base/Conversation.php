<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 14/09/16
 * Time: 10:02
 */

namespace Commands\Base;


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
            $this->start();

        return parent::update();
    }
}