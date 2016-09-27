<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\commands\system;

use app\commands\base\BaseSystemCommand;
use Longman\TelegramBot\Request;

/**
 * Start command
 */
class StartCommand extends BaseSystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'start';
    protected $description = 'Comando de start';
    protected $usage = '/start';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $text = '¡Bienvenido al bot de la ETSIINF '.$message->getChat()->getFirstName()."!\n".
            'Gracias por usarnos, para obtener la lista de los comandos disponibles escribe /help';

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
