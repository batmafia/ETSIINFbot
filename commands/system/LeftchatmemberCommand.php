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

/**
 * Left chat member command
 */
class LeftchatmemberCommand extends BaseSystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Leftchatmember';
    protected $description = 'Left Chat Member';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$member = $message->getLeftChatMember();
    }
}
