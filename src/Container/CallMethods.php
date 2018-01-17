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

use InvalidArgumentException;

/**
 * Call to methods to inject dependences in Containers
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */
class CallMethods
{
    /**
     * Call methods to inject dependences
     * 
     * @access public
     * @param Container $container 
     * @param string|callable $call 
     * @param array $paramerters
     * @param string|null $default
     * @return mixed 
     *      
    */
    
    public static function call(&$container, $call, array $parameters = [], $default = null)
    {
        if(self::isSignature($call)){
            return self::callClass($container, $call, $parameters, $default);
        }
         
        return self::methodClass($container, $call, $parameters, $default);
    }
     
    /**
     * Call method using Class@method syntax
     * 
     * @access private
     * @param Container $container 
     * @param string|callable $call 
     * @param array $paramerters
     * @param string|null $default
     * @return mixed 
     * 
     * @throws InvalidArgumentException
     * 
     * @see https://laravel.com/api/5.4/Illuminate/Container/BoundMethod.html
     * 
     */
    private static function callClass(&$container, $call, array $parameters = [], $default = null)
    {
        $segments = explode("@", $call);
        
        $method = count($segments) == 2 ? $segments[1] : $default;
        
        if(is_null($method)){
            throw new InvalidArgumentException("Method not defined.");
        }
        
        return self::call($container, [$segments[0], $method], $parameters); 
    }
     
    /**
     * Call method
     * 
     * @access private
     * @param Container $container 
     * @param string|callable $call 
     * @param array $paramerters
     * @param string|null $default
     * @return mixed  
     * 
     */
    private static function methodClass(&$container, $call, array $parameters = [], $default)
    {
        if(!$container->has($call[0])){ 
            $container->make($call[0], function() use ($call) {
               return new $call[0]; 
            });
        }
          
        $method = isset($call[1]) ? $call[1] : $default;
        
        return call_user_func_array([$container->get($call[0]), $method], $parameters);
    }
    
    /**
     * Determine if the given string is in Class@method syntax.
     *
     * @access public
     * @param  mixed  $call
     * @return bool
     */
    public static function isSignature($call)
    {
        return is_string($call) && strpos($call, "@") !== false;
    }
}
