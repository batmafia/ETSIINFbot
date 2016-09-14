<?php

namespace Commands\Base;

abstract class BaseUserCommand extends BaseRegularCommand {

    public function isUserCommand()
    {
        return true;
    }

}