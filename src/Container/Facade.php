<?php

/**
 * Simpla - Just a simple Framework
 *  
 * The MIT License (MIT)
 * For full copyright and license information, please see the LICENSE file
 * 
 * @link          https://simplaframework.com
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Simpla\Container;

use Simpla\Contracts\FacadeInterface;

/**
 * Define Facades
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */                
class Facade implements FacadeInterface   
{

    /**
     * {@inheritdoc} 
     */
    public static function __callStatic($name, $arguments)
    {
            /* @var $app App */
            global $app;
            $service_name = static::createService();
                    
            
            switch (count($arguments)) {
                    case 0:
                            return $app->get($service_name)->$name();
                    case 1:
                            return $app->get($service_name)->$name($arguments[0]);
                    case 2:
                            return $app->get($service_name)->$name($arguments[0],$arguments[1]);
                    case 3:
                            return $app->get($service_name)->$name($arguments[0], $arguments[1], $arguments[2]);
                    case 4:
                            return $app->get($service_name)->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                    default:
                            call_user_func_array($app->get($service_name)->$name, $arguments);
            }
    }

    /**
     * {@inheritdoc} 
     */
    public static function createService()
    {}

}
