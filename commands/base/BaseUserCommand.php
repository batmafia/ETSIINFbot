<?php

namespace Commands\Base;

abstract class BaseUserCommand extends BaseCommand {

    public function isUserCommand()
    {
        return true;
    }

}