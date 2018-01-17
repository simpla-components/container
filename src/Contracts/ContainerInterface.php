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
namespace Simpla\Contracts;


use Closure;

/**
 * Container that saves the services used in the Framework based in Pimple Container
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */  
interface ContainerInterface
{
    /**
     * Create a new Service
     * 
     * @access public
     * @param string $nameService Name to Service
     * @param object $service the service
     * @return void
     */
    public function make(string $nameService, $service = null);
    
    /**
     * Return provider name register or instance
     * 
     * @access public
     * @param string $nameService To return expecific provider
     * @return string|Service 
     */
    public function get(string $nameService = null);
    
    /**
     * Check a service
     * 
     * @access public
     * @param string $nameService
     * @return boolean 
     */
    public function has(string $nameService);
      
    /**
     * Create a new Service based in singleton concept
     * 
     * @access public
     * @param string $nameService Name to Service
     * @param object $service the service
     * @return void
     */
    public function singleton(string $nameService, $service);
    
    /**
     * Create a service based in Closure
     * 
     * @access public
     * @param Closure $callable A service definition 
     * @return callable The passed callable
     * 
     * @throws ExpectedInvokableException Service definition has to be a closure or an invokable object
     * 
     * @see https://github.com/silexphp/Pimple/blob/master/src/Pimple/Container.php
     */
    public function closure(Closure $callback);
    
    /**
     * Gets a parameter or the closure defining an object.
     *
     * @access public
     * @param string $name The unique identifier for the parameter or object 
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws UnknownIdentifierException If the identifier is not defined
     *
     * @see https://github.com/silexphp/Pimple/blob/master/src/Pimple/Container.php
     */
    public function raw(string $name);
     
    /**
     * Create Service and Call method using Class@method syntax
     * 
     * @access public
     * @param string $methods using Class@method syntax
     * @param array $params Parameters to method
     * @param string|null Parameters default
     * @return Service
     * 
     */
    public function call($methods, array $params = [], $default = null);

    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @access public
     * @param string   $name       The unique identifier for the object
     * @param Closure $callable A service definition to extend the original
     *
     * @return callable The wrapped callable
     *
     * @throws UnknownIdentifierException        If the identifier is not defined
     * @throws FrozenServiceException            If the service is frozen
     * @throws InvalidServiceIdentifierException If the identifier belongs to a parameter
     * @throws ExpectedInvokableException        If the extension callable is not a closure or an invokable object
     * 
     * @see https://github.com/silexphp/Pimple/blob/master/src/Pimple/Container.php
     */
    public function extend(string $name, Closure $callable);
    
    /**
     * Create Tag to a interface
     * 
     * @access public
     * @param string $name Tag name
     * @param string $interface Interface name
     * @return void
     */
    public function tagged(string $name, $interface);
}
