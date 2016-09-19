<?php

namespace app\commands\base;

abstract class BaseUserCommand extends BaseRegularCommand {

    public function isUserCommand()
    {
        return true;
    }

}