<?php

namespace Commands\Base;

use \Longman\TelegramBot\Commands\Command;

abstract class BaseCommand extends Command {

    public function isAdminCommand()
    {
        return false;
    }

    public function isUserCommand()
    {
        return false;
    }

    public function isSystemCommand()
    {
        return false;
    }

}