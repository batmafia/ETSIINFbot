<?php

namespace app\commands\base;

use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;

class Request
{

    public $data = [];

    function __construct($chatId)
    {
        \Longman\TelegramBot\Request::initialize(\Yii::$app->bot);
        $this->data['chat_id'] = $chatId;
    }

    public function chatId($chatId)
    {
        $this->data['chat_id'] = $chatId;
        return $this;
    }

    public function keyboard($keyboard)
    {
        $this->data['reply_markup'] = new ReplyKeyboardMarkup(
            [
                'keyboard' => $keyboard ,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'selective' => true
            ]
        );
        return $this;
    }

    public function locationKeyboard()
    {
        $this->data['reply_markup'] = new ReplyKeyboardMarkup([
            'keyboard' => [[
                [ 'text' => 'Share Location', 'request_location' => true ],
            ]],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
            'selective'         => true,
        ]);
        return $this;
    }

    public function contactKeyboard()
    {
        $this->data['reply_markup'] = new ReplyKeyboardMarkup([
            'keyboard' => [[
                [ 'text' => 'Share Contact', 'request_contact' => true ],
            ]],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
            'selective'         => true,
        ]);
        return $this;
    }

    public function hideKeyboard()
    {
        $this->data['reply_markup'] = new ReplyKeyboardHide(['selective' => true]);
        return $this;
    }

    public function caption($caption)
    {
        $this->data['caption'] = strlen($caption)>200 ? substr($caption, 0, 200) : $caption;
        return $this;
    }

    public function sendMessage($message)
    {
        $this->data['text'] = $message;
        $result = \Longman\TelegramBot\Request::sendMessage($this->data);

        $this->data = [];

        return $result;
    }

    public function sendPhoto($photoId)
    {
        $this->data['photo'] = $photoId;
        $result = \Longman\TelegramBot\Request::sendPhoto($this->data);

        $this->data = [];

        return $result;
    }

    public function sendDocument($file)
    {
        $result = \Longman\TelegramBot\Request::sendDocument($this->data, $file);

        $this->data = [];

        return $result;
    }

}