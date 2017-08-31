<?php

namespace app\commands\base;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;

class Request
{

    public $data = [];

    function __construct($chatId=null)
    {
        \Longman\TelegramBot\Request::initialize(\Yii::$app->bot);

        if($chatId !== null)
            $this->chatId($chatId);
    }

    public function chatId($chatId)
    {
        $this->data['chat_id'] = $chatId;
        return $this;
    }

    public function replyTo($messageId)
    {
        $this->data['reply_to_message_id'] = $messageId;
        return $this;
    }

    public function keyboard($keyboard)
    {

        $this->data['reply_markup'] = new Keyboard(
            [
                'keyboard' => $keyboard ,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'selective' => true
            ]
        );

        return $this;
    }

    public function markdown()
    {
        $this->data['parse_mode'] = 'Markdown';
        return $this;
    }

    public function locationKeyboard()
    {
        $this->data['reply_markup'] = new Keyboard([
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
        $this->data['reply_markup'] = new Keyboard([
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
        $this->data['reply_markup'] = new Keyboard([
            'keyboard' => [],
            'remove_keyboard' => true,
            'selective' => false
        ]);
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

        $this->reset();

        return $result;
    }

    public function sendPhoto($photoId)
    {
        $this->data['photo'] = $photoId;
        $result = \Longman\TelegramBot\Request::sendPhoto($this->data);

        $this->reset();

        return $result;
    }

    public function sendDocument($file)
    {
        $this->data['file'] = $file;
        $result = \Longman\TelegramBot\Request::sendDocument($this->data);

        $this->data = [];
        $this->reset();

        return $result;
    }

    const ACTION_TYPING = "typing";
    const ACTION_UPLOADING_PHOTO = 'upload_photo';
    const ACTION_RECORDING_VIDEO = 'record_video';
    const ACTION_UPLOADING_VIDEO = 'upload_video';
    const ACTION_RECORDING_AUDIO = 'record_audio';
    const ACTION_UPLOADING_AUDIO = 'upload_audio';
    const ACTION_UPLOADING_DOCUMENT = 'upload_document';
    const ACTION_FINDING_LOCATION = 'find_location';
    public function sendAction($action)
    {
        $this->data['action'] = $action;
        $result = \Longman\TelegramBot\Request::sendChatAction($this->data);

        $this->reset();

        return $result;
    }

    private function reset()
    {
        if(isset($this->data['chat_id']))
            $this->data = ['chat_id'=>$this->data['chat_id']];
        else
            $this->data = [];
    }

}