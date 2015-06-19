<?php

namespace JildertMiedema\LaravelTactician;

use ArrayAccess;

trait DispatchesCommands
{
    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param $command
     */
    protected function dispatch($command)
    {
        app('tactician.dispatcher')->dispatch($command);
    }

    protected function dispatchFrom($command, ArrayAccess $source, array $extras = [])
    {
    	app('tactician.dispatcher')->dispatchFrom($command, $source, $extras);
    }
}
