<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\commands\user;

use app\commands\base\BaseUserCommand;
use app\models\Chat;
use app\models\Message;
use app\models\User;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;

/**
 * User "/cancel" command
 *
 * This command cancels the currently active conversation and
 * returns a message to let the user know which conversation it was.
 * If no conversation is active, the returned message says so.
 */
class BroadcastCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'broadcast';
    protected $description = 'Enviar mensajes a todos los suscriptores del bot.';
    protected $usage = '/broadcast';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/

    const SEND = "Enviar";
    const REMOVE_LAST = "Borrar último mensaje";
    const PREVIEW = "Previsualizar";
    const EDIT = "Seguir editando";
    const CANCEL = "Cancelar";

    /**
     * {@inheritdoc}
     */
    public function processStart()
    {
        $this->getConversation();
        $this->nextStep();
    }

    public function processRequests($message, $text)
    {
        $opts = [self::PREVIEW, self::CANCEL];
        if(isset($this->getConversation()->notes['messages']) && count($this->getConversation()->notes['messages'])>0)
            array_unshift($opts, self::REMOVE_LAST);

        $this->getRequest()->keyboard(array_chunk($opts, 2));
        if($this->isProcessed())
        {
            return $this->getRequest()->sendMessage("Envía los mensajes que quieras y finaliza con Enviar");
        }
        if($text === self::REMOVE_LAST)
        {
            array_pop($this->getConversation()->notes['messages']);
            return $this->getRequest()->sendMessage("Último mensaje eliminado");
        }
        if($text === self::PREVIEW)
        {
            return $this->nextStep();
        }
        if($text === self::CANCEL)
        {
            return $this->cancelConversation();
        }

        if(count($opts) < 3)
        {
            array_unshift($opts, self::REMOVE_LAST);
            $this->getRequest()->keyboard(array_chunk($opts, 2));
        }

        $this->getConversation()->notes['messages'][] = $message->getMessageId();
        $this->getRequest()->sendMessage("Mensaje añadido");
    }

    public function processPreview($text, $chat)
    {
        $opts = [self::SEND, self::EDIT];
        $this->getRequest()->keyboard(array_chunk($opts, 1));

        if($this->isProcessed() || !in_array($text, $opts))
        {
            $this->sendMessages($chat->getId());
            if(isset($this->getConversation()->notes['messages']))
            {
                return $this->getRequest()->sendMessage("-\n-\nQuieres enviar estos mensajes o editarlos?");
            }

            return $this->previousStep();
        }
        if($text === self::EDIT)
        {
            return $this->previousStep();
        }

        return $this->nextStep();
    }

    public function processSend($chat)
    {
        $number = 0;

        $ids = Chat::find()->select("chat.id")->joinWith("users")->where('broadcast')->andWhere("chat.id<>:id",['id'=>$chat->getId()])->all();

        foreach ($ids as $id)
        {
            $n = $this->sendMessages($id->id);
            $number++;
        }

        $this->stopConversation();
        return $this->getRequest()->hideKeyboard()->sendMessage("Enviado a $number personas.");
    }

    private function sendMessages($chatId)
    {
        if(!isset($this->getConversation()->notes['messages']))
        {
            $this->getRequest()->sendMessage("No hay mensajes que enviar");
            return 0;
        }

        $query = Message::find()->asArray();
        foreach ($this->getConversation()->notes['messages'] as $i=>$id)
        {
            $query->orWhere("id=:id$i",["id$i"=>$id]);
        }
        $messages = $query->all();

        $number = 0;
        foreach($messages as $req)
        {
            $req['chat_id'] = $chatId;
            $action = 'send'.ucfirst($this->messageToRequest($req));

            $result = forward_static_call(['Longman\TelegramBot\Request', $action], $req);

            if($result->isOk())
            {
                $number++;
            }
        }

        return $number;
    }


    private function messageToRequest(&$req)
    {
        $req = array_filter($req, function ($var)
        {
            return $var != '';
        });
        unset($req['id']);
        unset($req['user_id']);

        if(isset($req['text']))
        {
            return 'message';
        }
        if(isset($req['photo']))
        {
            $photo = \GuzzleHttp\json_decode($req['photo']);
            if(is_array($photo))
            {
                $req['photo'] = end($photo)->file_id;
            }
            return 'photo';
        }
        if(isset($req['voice']))
        {
            $voice = \GuzzleHttp\json_decode($req['voice']);
            $req['voice'] = $voice->file_id;
            return 'voice';
        }
        if(isset($req['sticker']))
        {
            $sticker = \GuzzleHttp\json_decode($req['sticker']);
            $req['sticker'] = $sticker->file_id;
            return 'sticker';
        }
        if(isset($req['audio']))
        {
            $audio = \GuzzleHttp\json_decode($req['audio']);
            $req['audio'] = $audio->file_id;
            return 'audio';
        }
        if(isset($req['document']))
        {
            $document = \GuzzleHttp\json_decode($req['document']);
            $req['document'] = $document->file_id;
            return 'document';
        }
        if(isset($req['video']))
        {
            $video = \GuzzleHttp\json_decode($req['video']);
            $req['video'] = $video->file_id;
            return 'video';
        }
        if(isset($req['contact']))
        {
            $contact = \GuzzleHttp\json_decode($req['contact'], true);
            unset($req['contact']);
            foreach(['phone_number','first_name','last_name','user_id'] as $param)
            {
                if(isset($contact[$param]))
                {
                    $req[$param] = $contact[$param];
                }
            }
            return 'contact';
        }
        if(isset($req['location']))
        {
            $location = \GuzzleHttp\json_decode($req['location']);
            unset($req['location']);
            $req['latitude'] = $location->latitude;
            $req['longitude'] = $location->longitude;
            return 'location';
        }
    }


}
