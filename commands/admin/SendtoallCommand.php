<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\commands\admin;

use app\commands\base\BaseAdminCommand;
use Longman\TelegramBot\Request;

/**
 * Admin "/sendtoall" command
 */
class SendtoallCommand extends BaseAdminCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'sendtoall';
    protected $description = 'Envia el mensaje a todos los usuarios del bot.';
    protected $usage = '/sendtoall <Mensaje a enviar>';
    protected $version = '1.2.1';
    protected $need_mysql = true;
    /**#@-*/

    /**
     * Execute command
     *
     * @return boolean
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $text = $message->getText(true);

        if ($text === '') {
            $text = 'Write the message to send: /sendtoall <message>';
        } else {
            $results = Request::sendToActiveChats(
                'sendMessage', //callback function to execute (see Request.php methods)
                ['text' => $text], //Param to evaluate the request
                true, //Send to groups (group chat)
                true, //Send to super groups chats (super group chat)
                true, //Send to users (single chat)
                null, //'yyyy-mm-dd hh:mm:ss' date range from
                null  //'yyyy-mm-dd hh:mm:ss' date range to
            );

            $tot = 0;
            $fail = 0;

            $text = 'Mensaje enviado a:' . "\n";
            foreach ($results as $result) {
                $status = '';
                $type = '';
                if ($result->isOk()) {
                    $status = '✔️';

                    $ServerResponse = $result->getResult();
                    $chat = $ServerResponse->getChat();
                    if ($chat->isPrivateChat()) {
                        $name = $chat->getFirstName();
                        $type = 'user';
                    } else {
                        $name = $chat->getTitle();
                        $type = 'chat';
                    }
                } else {
                    $status = '✖️';
                    ++$fail;
                }
                ++$tot;

                $text .= $tot . ') ' . $status . ' ' . $type . ' ' . $name . "\n";
            }
            $text .= 'Entregado: ' . ($tot - $fail) . '/' . $tot . "\n";

            if ($tot === 0) {
                $text = 'No se han encontrado usuarios o chats.';
            }
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
