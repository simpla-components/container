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

use Simpla\Contracts\ServiceProviderInterface;
/**
 * Methods to use in Services Providers in Container
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */  
interface ContainerProviderInterface
{
    
    /**
     * Register a ServicesProvider 
     * @access public
     */
    public function register(ServiceProviderInterface $serviceProvider);
    
    /**
     * This method registers the providers 
     * in an associative array.  The key of the array 
     * must be the key in the container you want to store
     * it as, and the value is the instance of the new Class
     * 
     * @access public
     * @param array $providers Get all providers
     */
    public function registerProviders(array $providers);
     
    /**
    * createAlias() method
    *
    * This method will create the aliases for the 
    * given alias=>class array
    * @param array
    */ 
    public function createAlias(array $aliases);
    
    /**
    * alias() method
    * This method will take one single alias and class
    * and make an alias for it
    * @param string $alias alias name 
    * @param sring $class class name
    */ 
    public function alias($alias, $class);    
}
