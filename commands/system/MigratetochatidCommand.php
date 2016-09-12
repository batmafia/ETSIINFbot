<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Commands\System;

use Commands\Base\BaseSystemCommand;
use Longman\TelegramBot\Commands\SystemCommand;

/**
 * Migrate to chat id command
 */
class MigratetochatidCommand extends BaseSystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Migratetochatid';
    protected $description = 'Migrate to chat id';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$migrate_to_chat_id = $message->getMigrateToChatId();
    }
}
