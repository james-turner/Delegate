<?php

/**
 * DelegateClass
 * @copyright James Turner 2012
 * @wiki http://github.com/james-turner/Delegate
 *
 */
class DelegateClass {

    /**
     * @var array
     */
    private $delegates = array();

    /**
     * @param null|array|string|object|*args $classname_or_object_or_array
     */
    public function __construct($classname_or_object_or_array = null){
        foreach(func_get_args() as $arg){
            if(null === $arg) continue;
            $delegate = $arg;
            # Power up!
            if(is_string($delegate)){
                $delegate = new $delegate();
            }
            if(!is_array($delegate)){
                $delegate = array($delegate);
            }
            $this->delegates = array_merge($this->delegates, $delegate);
        }
    }

    /**
     * Bind class functionality at runtime.
     * @param $class
     * @return void
     */
    public function bind($class){
        $this->unbind($class);
        $this->delegates[] = $class;
    }

    /**
     * Unbind class functionality at runtime.
     * @param $class
     * @return void
     */
    public function unbind($class){
        if(false !== ($key = array_search($class, $this->delegates))){
            unset($this->delegates[$key]);
        }
    }

    /**
     * @throws BadMethodCallException
     * @param string $method_name
     * @param array $args
     * @return mixed
     */
    public function __call($method_name, $args){
        foreach($this->delegates as $delegate){
            $reflection = new ReflectionObject($delegate);
            if($reflection->hasMethod($method_name)){
                $reflectMethod = $reflection->getMethod($method_name);
                // Check param matching
                foreach($reflectMethod->getParameters() as $idx => $param){
                    if($param->getClass()){
                        $className = $param->getClass()->name;
                        if(!$args[$idx] instanceof $className){
                            continue 2; // keep trying delegates
                        }
                    }
                }
                // check if the number of args is within the allowed number
                $num_of_required = $reflectMethod->getNumberOfRequiredParameters();
                $num_of_params = $reflectMethod->getNumberOfParameters();
                if(count($args) >= $num_of_required && count($args) <= $num_of_params){
                    $reflectMethod->setAccessible(true);
                    return $reflectMethod->invokeArgs($delegate, $args);
                }
            }
        }
        throw new BadMethodCallException("Unknown method '$method_name()'.");
    }

    public static function __callStatic($name, $arguments){
        throw new BadMethodCallException("Delegate class does not cater for static method invocation at present.");
    }

    /**
     * Check if any delegate will respond to the supplied method name.
     * @param string $method_name
     * @return bool
     */
    public function respond_to($method_name){
        foreach($this->delegates as $delegate){
            $reflection = new ReflectionObject($delegate);
            if($reflection->hasMethod($method_name) || (method_exists($delegate, __METHOD__) && $delegate->{__METHOD__}($method_name))){
                return true;
            }
        }
        return false;
    }

    /**
     * Invoke a method given by the name with all following parameters as the arguments.
     * @throws InvalidArgumentException
     * @param $method_name
     * @return mixed
     */
    public function send($method_name){
        $args = func_get_args();
        array_shift($args); # pop method name off the front
        if($this->respond_to($method_name)){
            return call_user_func_array(array($this, $method_name), $args);
        }
        throw new InvalidArgumentException("Unknown method '$method_name()' supplied as argument");
    }

}