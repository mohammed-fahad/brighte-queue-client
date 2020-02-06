<?php

namespace BrighteCapital\QueueClient\container;

class Container
{
    protected static $container = [];
    /*
     * This helps pull the bound service instead of creating a new service with each get
     * E.g bind('servicexName', servicex) you  can be certain, when you do get on servicexName,
     * you will always get the same service object.
     * You can override the service by binding your implementation of the service by binding it
     * */
    protected static $services = [];

    protected static $instance;

    /**
     * Container constructor.
     * Singleton....should not instantiate new instance of this.
     */
    private function __construct()
    {
    }

    /**
     * @param string $key alias/name for the service
     * @param callable $callback callable
     * @return void
     */
    public function bind($key, callable $callback)
    {
        if (isset(self::$services[$key])) {
            unset(self::$services[$key]);
        }

        self::$container[$key] = $callback;
    }

    /**
     * Get an instance by service name, the same instance will be returned if previously requested
     *
     * @param string $key alias/name for the service
     * @param mixed ...$args variadic arguments to the service
     * @return callable
     * @throws \Exception
     */
    public function get($key, ...$args)
    {
        if (!isset(self::$services[$key])) {
            self::$services[$key] = $this->getFresh($key, ...$args);
        }

        return self::$services[$key];
    }

    /**
     * Get a fresh instance by service name
     *
     * @param string $key alias/name for the service
     * @param mixed ...$args variadic arguments to the service
     * @return callable
     * @throws \Exception
     */
    public function getFresh($key, ...$args)
    {
        if (!isset(self::$container[$key])) {
            throw new \InvalidArgumentException(sprintf(" %s binding does not exist in container.", $key));
        }

        $callback = self::$container[$key];

        return $callback(...$args);
    }

    /**
     * @param string $key key
     * @return bool boolean
     */
    public function remove(string $key)
    {
        if (isset(self::$container[$key])) {
            unset(self::$container[$key]);
            unset(self::$services[$key]);

            return true;
        }

        return false;
    }

    /**
     * @return array $container returns all the bindings in the container
     */
    public function getBindings(): array
    {
        return self::$container;
    }

    /**
     * Remove everything from the container
     * @return void
     */
    public function reset()
    {
        self::$container = [];
        self::$services = [];
        self::$instance = null;
    }

    /**
     * @param string $key key
     * @return bool
     */
    public function has($key)
    {
        if (isset(self::$services[$key])) {
            return true;
        }
        if (isset(self::$container[$key])) {
            return true;
        }

        return false;
    }


    /**
     * @return \BrighteCapital\QueueClient\container\Container
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
