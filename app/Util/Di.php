<?php
namespace App\Util;

/**
 * @codeCoverageIgnore
 */
class Di
{
    private static $objects = [];
    private static $handlers = [];
    private static $instance;
    
    /**
     *
     * @return Di
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Di();
        }
        return self::$instance;
    }
    
    public function set($className, $object)
    {
        self::$objects[$className] = $object;
    }
    
    public function create($className)
    {
        if (!isset(self::$handlers[$className]) && !class_exists($className)) {
            throw new \Exception("Cannot instantiate $className, no handler found");
        } elseif (!isset(self::$handlers[$className]) && class_exists($className)) {
            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            $arguments = [];

            if ($constructor) {
                $constructorParams = $constructor->getParameters();

                foreach ($constructorParams as $s => $param) {
                    $paramType = $param->getClass()->getName();
                    if (class_exists($paramType)) {
                        $arguments[] = $this->get($paramType);
                    } else {
                        throw new \Exception("Cannot auto-resolve parameter $param of $className; define a explicit handler instead.");
                    }
                }
            } else {
                $arguments = [];
            }

            $object = $reflection->newInstanceArgs($arguments);
        } else {
            $callable = self::$handlers[$className];
            $object = $callable($this);
        }
        
        return $object;
    }
    
    public function get($className)
    {
        if (!isset(self::$objects[$className])) {
            self::$objects[$className] = $this->create($className);
        }
        
        return self::$objects[$className];
    }
    
    public function register($className, callable $callback)
    {
        self::$handlers[$className] = $callback;
    }
}
