<?php

namespace app\commands\base;

abstract class BaseSystemCommand extends BaseCommand {

    public function isSystemCommand()
    {
        return true;
    }

}