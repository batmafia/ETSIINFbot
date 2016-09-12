<?php

namespace Commands\Base;

abstract class BaseSystemCommand extends BaseCommand {

    public function isSystemCommand()
    {
        return true;
    }

}