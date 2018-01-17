<?php

/**
 * Simpla - Just a simple Framework
 *  
 * The MIT License (MIT)
 * For full copyright and license information, please see the LICENSE file
 * 
 * @link          [Site-Simpla]
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Simpla\Container;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Resolver Contruct and method inhjections
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */
class Resolver {

    /**
    * Build an instance of the given class
    * 
    * @param string $class
    * @return mixed
    *
    * @throws Exception
    */
    public function resolve($class)
    {
        $reflector = new ReflectionClass($class);

        if(!$reflector->isInstantiable()){
            throw new Exception("[$class] is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if(is_null($constructor)){
                return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Build up a list of dependencies for a given methods parameters
     *
     * @param array $parameters
     * @return array
     */
    public function getDependencies($parameters)
    {
        $dependencies = array();

        foreach($parameters as $parameter){
            $dependency = $parameter->getClass();

            if(is_null($dependency)){
                $dependencies[] = $this->resolveNonClass($parameter);
            }
            else{
                $dependencies[] = $this->resolve($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * Determine what to do with a non-class value
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws Exception
     */
    public function resolveNonClass(ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable()){
            return $parameter->getDefaultValue();
        }

        throw new Exception("Erm.. Cannot resolve the unkown!?");
    }
    
    /**
     * Resolve methods dinamicaly
     * 
     * @access public
     * @param object $name 
     * @param string $method
     */
    public function resolveMethods($name, $method)
    {
        $parameters = [];
        
        $reflection = new ReflectionMethod($name, $method);
          
        foreach ($reflection->getParameters() as $key => $parameter) {
            $class = $parameter->getClass();
          
            $parameters[$parameter->name] = new $class->name; 
        } 
        
        call_user_func_array([$name, $method], $parameters);
    }
}