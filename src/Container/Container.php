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

use Closure;
use ArrayAccess;
use ReflectionClass;
use Xtreamwayz\Pimple\Container as Pimple;
use Simpla\Contracts\ContainerInterface;
use Simpla\Contracts\ServiceProviderInterface;
use Simpla\Contracts\ContainerProviderInterface;
use Xtreamwayz\Pimple\Exception\NotFoundException;
use Simpla\Exceptions\Container\MissingAliasesException;

/**
 * {@inheritdoc} 
 */
class Container implements ContainerInterface, ArrayAccess
{
    const PROVIDER_SUFFIX = "ServiceProvider";
    const FACADE_SUFFIX = "Facade";


    /**
     *  @var $container \Xtreamwayz\Pimple\Container 
     */
    protected $container;  
     
    protected $tag;
    
    protected $providers;
 
    protected $defer;
    
    protected $aliases;

    private static $instance;

    /**
     * @access public
     * @param Xtreamwayz\Pimple\Container $container
     */
    protected function __construct(Pimple $container)
    {
        $this->container = $container;        
    }
    
    final private function __clone()
    {}
    
    final private function __wakeup()
    {}
    
     /**
     * Set single instance of Container
     * 
     * @param string $pimple default base container of application
     * @return Configurator $instance
     */
    public static function instance(Pimple $Pimple)
    {
        if(!isset(self::$instance)){
            self::$instance = new static($Pimple);
        }
        
        return self::$instance;
    }      

    /**
     * {@inheritdoc} 
     */
    public function make(string $nameService, $service = null)
    { 
        if(is_null($nameService)){
            $this->container[$nameService] = $this->container->factory(function() use ($nameService){
                return new $nameService;
            });
            
            return true;
        }
        
        if(is_string($service)){  
            $this->container[$nameService] = $this->container->factory(function() use ($service){
                return new $service;
            });
            
            return true;
        }
        
        if(!is_callable($service)){
            $this->container[$nameService] = $this->container->factory(function() use ($service){
                return $service;
            });
            
            return true;
        }
        
        $this->container[$nameService] = $this->container->factory($service);
    } 
      
    /**
     * {@inheritdoc} 
     */
    public function singleton(string $nameService, $service)
    { 
        $this->container->offsetSet($nameService, $service);
    }
    
    /**
     * {@inheritdoc} 
     */
    public function call($methods, array $params = [], $default = null)
    {
        return CallMethods::call($this, $methods, $params, $default);
    }     
      
    /**
     * {@inheritdoc} 
     */
    public function closure(Closure $callback)
    {
        return $this->container->protect($callback);
    } 
      
    /**
     * {@inheritdoc} 
     */
    public function raw(string $name)
    { 
        return $this->container->raw($name);
    }

    /**
     * {@inheritdoc} 
     */
    public function extend(string $name, Closure $callable)
    {
        return $this->container->extend($name, $callable);
    }
 
    /**
     * {@inheritdoc} 
     */
    public function tagged(string $name, $interface)
    {
        $this->tag[$name] = $interface;
    }
    
    /**
     * Get Interfaces by tag name
     * 
     * @access public
     * @param string|null $name Tag name 
     * @return string|array
     */
    public function getTag(string $name = null)
    {
        return is_null($name) ? $this->tag
                               : $this->tag[$name];
    }
    
    /**
     * Check if tag name exists
     * 
     * @access public
     * @param string $name Tag name 
     * @return boolean
     */
    public function hasTag(string $name)
    {
        return isset($this->tag[$name]);
    }    
    
    /**
     * {@inheritdoc} 
     */ 
    public function get(string $nameService = null)
    {
        if(is_null($nameService)){
            return $this->container->keys();
        } 
        
        $this->providerResolver($nameService);
        
        try {         
            if($this->has($nameService)){
                return $this->container->get($nameService);
            }      
            
            if($this->hasTag($nameService)){
                return $this->container->get($this->tag[$nameService]) ;
            } 
        }
        catch (NotFoundException $exc) {
            echo $exc->getTraceAsString();
        }
    }
 
    /**
     * {@inheritdoc} 
     */
    public function has(string $nameService) 
    {
        return $this->container->has($nameService);
    }
    
    /**
     * Unset service
     * 
     * @access public
     * @param string $nameService Service name 
     * @return void
     */    
    public function unmake(string $nameService)
    {
        unset($this->container[$nameService]);
    }
    
    
    /**
     * This method registers the providers 
     * in an associative array.  The key of the array 
     * must be the key in the container you want to store
     * it as, and the value is the instance of the new Class
     * Define providers and with/without defer and load providers
     * 
     * @access public
     * @param array $providers Get all providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $class) {
            $name = explode("\\", $class);
            $className = end($name);
            
            $serviceName = $this->checkNameServices($className);
             
            if((is_int(strpos($class, self::PROVIDER_SUFFIX)))){
                
                $this->providers[$className] = $class; 
                $classService = new ReflectionClass($class); 
                    
                if(property_exists($class, 'defer')){
                   
                    $reflectionProperty = $classService->getProperty("defer");                    
                    $reflectionProperty->setAccessible(true);
                    $defer = $reflectionProperty->getValue(new $class);
                    
                    if($defer){
                        $nameService = $class::providers(); 
                        $this->defer[$class] = $nameService; 
                        continue;
                    }
                } 
            } 
             
            if(isset($this->container[$serviceName])){
                continue;
            }
                
            $this->container[$serviceName] = $this->container->factory(function()
                use ($class){ 
                    return new $class();
            }); 
        }
 
        $this->registers();   
    } 
    
    /**
     * Get providers loaded
     * 
     * @access public 
     * @return string|array
     */
    public function getProviders()
    {
        return $this->providers;
    }
    
    /**
     * Get deferred providers loaded
     * 
     * @access public 
     * @return string|array
     */
    public function getDeferredServices()
    {
        return $this->defer;
    }
    
    /**
     * Checks whether an alias exists for a class or service provider 
     * based on its name, comparing it with the name of a facade.
     * 
     * @access private
     * @param string $class class name
     * @return string If it finds a corresponding alias, it returns it, 
     * otherwise it returns the name of the class itself.
     */
    private function checkNameServices($class)
    {
        $facade = ""; 
        if(is_null($this->aliases)){
            throw new MissingAliasesException("Initialization Error. Aliases have not been initialized correctly.");
        }
        
        foreach ($this->aliases as $alias => $facadeName) {
            $name = explode("\\", $facadeName);
            $classNameFacade = end($name);
              
            if(($class.self::FACADE_SUFFIX == $classNameFacade) || 
               ($class == $classNameFacade)){
                $facade = $alias;
            }
            
            $className = str_replace(self::PROVIDER_SUFFIX, "", $class);
            $getCorretName = str_replace([self::PROVIDER_SUFFIX,self::FACADE_SUFFIX], ["",""], $classNameFacade);           
            
            if($className == $getCorretName){
                $facade = $alias;
            }
        } 
        
        return (empty($facade)) ? $class : $facade;
    }    
    
   /**
    * Register instance of ServiceProvider
    * 
    * @access public
    * @param ServiceProviderInterface $serviceProvider
    * @return void 
    *      
    */    
    public function register(ServiceProviderInterface $serviceProvider)
    {
        $serviceProvider->register($this);
    } 
    
    /**
     * Load register and boot method by serviceProviders
     * 
     * @access private
     * @param string $provider
     */
    private function registryResolver($provider)
    {
        $service = $this->get($provider);
        
        if(!is_object($service)){
            return false;
        }
        
        if((is_int(strpos(get_class($service), self::PROVIDER_SUFFIX)))){
            $service->register($this); 

            if(method_exists($service, 'boot')){
                $service->boot();
            } 
        } 
    }
    

    /**
     * Autoregister ServicesProviders
     * 
     * @access protected
     */
    protected function registers()
    {  
        foreach ($this->get() as $provider) { 
            $this->registryResolver($provider);            
        }
    }            
    
    /**
     * Check in defer array the ServiceProvider name
     * 
     * @access private
     * @param string $nameService
     * @return string
     */
    private function getServiceProviderDefer(string $nameService)
    {  
        
       foreach($this->defer as $key => $provider){ 
           if(in_array($nameService, $provider)){
               return $key;
           }
        }
    }
     
    /**
     * Resolver and registry serviceproviders
     * 
     * @access private
     * @param string $nameService
     */
    private function providerResolver(string $nameService)
    { 
        if($this->has($nameService)){
            return false;
        }
        
        if(is_null($this->defer)){
            return false;
        }
        
        $serviceProvider = $this->getServiceProviderDefer($nameService);
         
        $this->container[$serviceProvider] =  $this->container->factory(function()
                use ($serviceProvider){

            return new $serviceProvider();
        });  
 
        $this->registryResolver($serviceProvider);   
    }        
    
    public function offsetExists($offset)            
    {
        return $this->container->offsetExists($offset);
    }   
    
    public function offsetGet($offset)
    {
        $this->providerResolver($offset);
        
        if(!$this->has($offset)){
            return $this->container->offsetGet($this->tag[$offset]);
        }
            
        return $this->container->offsetGet($offset);
    }   
    
    public function offsetSet($offset, $value)
    { 
        $this->container[$offset] = $value; 
    }   
    
    public function offsetUnset($offset)
    {
        $this->container->offsetUnset($offset);
    } 

     
    /**
    * createAlias() method
    *
    * This method will create the aliases for the 
    * given alias=>class array
    * @param array
    */ 
    public function createAlias(array $aliases)
    {
        $this->aliases += $aliases;
        
        foreach ($aliases as $alias=>$class)
        {
            class_alias($class, $alias);
        }
    }
    
    /**
    * alias() method
    * This method will take one single alias and class
    * and make an alias for it
    * @param string $alias alias name 
    * @param sring $class class name
    */ 
    public function alias($alias, $class)
    {
        $this->aliases[$alias] = $class;
        
        class_alias($class, $alias);
    }       
    
    /**
     * Get the aliases
     * 
     * @access public
     * @param string $name Name Alias
     */
    public function getAlias(string $name = null)
    {
        return is_null($name) ? $this->aliases : $this->aliases[$name];
    } 
}
