<?php

namespace Commands\Base;

abstract class BaseAdminCommand extends BaseCommand {

    public function isAdminCommand()
    {
        return true;
    }

}