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

use Simpla\Contracts\ServiceProviderInterface;
/**
 * Description of SirviceProvider
 * @package 
 * @subpackage 
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    
    public function register(\Simpla\Contracts\ContainerInterface $container)
    {
        
    }
}
