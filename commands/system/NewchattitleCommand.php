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

/**
 * New chat title command
 */
class NewchattitleCommand extends BaseSystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Newchattitle';
    protected $description = 'New chat Title';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$new_chat_title = $message->getNewChatTitle();
    }
}
