<?php

namespace JildertMiedema\LaravelTactician;

use ArrayAccess;
use ReflectionClass;
use ReflectionParameter;
use League\Tactician\CommandBus;

class Dispatcher
{
    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($command)
    {
        $this->bus->handle($command);
    }

    /**
     * Marshal a command and dispatch it
     * 
     * @param  mixed       $command
     * @param  ArrayAccess $source
     * @param  array       $extras
     * 
     * @return mixed
     */
    public function dispatchFrom($command, ArrayAccess $source, array $extras = [])
    {
        return $this->dispatch($this->marshal($command, $source, $extras));
    }

    /**
     * Marshal a command from the given array accessible object
     * 
     * @param  mixed       $command
     * @param  ArrayAccess $source
     * @param  array       $extras
     * 
     * @return mixed
     */
    protected function marshal($command, ArrayAccess $source, array $extras = [])
    {
        $injected   = [];
        $reflection = new ReflectionClass($command);

        if ($constructor = $reflection->getConstructor()) {

            $injected = array_map(function ($parameter) use ($command, $source, $extras) {
                return $this->getParameterValueForCommand($command, $source, $parameter, $extras);
            }, $constructor->getParameters());

        }

        return $reflection->newInstanceArgs($injected);        
    }

    /**
     * Get a parameter value for a marshaled command.
     *
     * @param  string              $command
     * @param  ArrayAccess         $source
     * @param  ReflectionParameter $parameter
     * @param  array               $extras
     * @return mixed
     */
    protected function getParameterValueForCommand($command, ArrayAccess $source, ReflectionParameter $parameter, array $extras = [])
    {
        if (array_key_exists($parameter->name, $extras)) {

            return $extras[$parameter->name];

        }

        if (isset($source[$parameter->name])) {

            return $source[$parameter->name];

        }

        if ($parameter->isDefaultValueAvailable()) {

            return $parameter->getDefaultValue();

        }
        
        MarshalException::whileMapping($command, $parameter);
    }
}
