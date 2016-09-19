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
 * Migrate from chat id command
 */
class MigratefromchatidCommand extends BaseSystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Migratefromchatid';
    protected $description = 'Migrate from chat id';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$migrate_from_chat_id = $message->getMigrateFromChatId();
    }
}
