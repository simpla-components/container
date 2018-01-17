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

namespace Simpla\Contracts;

/**
 * Define Facades
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */      
interface FacadeInterface
{ 
    /**
     * Create Facade based on a Service
     * 
     * @return string Name of tha Facade
     */
    public static function createService();
}
