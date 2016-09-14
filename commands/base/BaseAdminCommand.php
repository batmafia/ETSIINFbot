<?php

namespace Commands\Base;

abstract class BaseAdminCommand extends BaseRegularCommand {

    public function isAdminCommand()
    {
        return true;
    }

}