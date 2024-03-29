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
use Longman\TelegramBot\Conversation;

/**
 * User "/cancel" command
 *
 * This command cancels the currently active conversation and
 * returns a message to let the user know which conversation it was.
 * If no conversation is active, the returned message says so.
 */
class CancelCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'cancel';
    protected $description = 'Cancela la coversación actualmente activa con el bot.';
    protected $usage = '/cancel';
    protected $version = '0.1.1';
    protected $need_mysql = true;
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $text = '¡No existen conversaciones activas!';

        //Cancel current conversation if any
        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        if ($conversation_command = $conversation->getCommand()) {
            $conversation->cancel();
            $text = '¡Conversación "' . $conversation_command . '" cancelada!';
        }

        return $this->getRequest()->hideKeyboard()->sendMessage($text);
    }

    /**
     * {@inheritdoc}
     */
    public function executeNoDb()
    {
        return $this->sendMessage('No hay nada que cancelar.');
    }

}
