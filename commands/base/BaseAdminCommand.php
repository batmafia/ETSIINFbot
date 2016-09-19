<?php

namespace app\commands\base;

abstract class BaseAdminCommand extends BaseRegularCommand {

    public function isAdminCommand()
    {
        return true;
    }

}