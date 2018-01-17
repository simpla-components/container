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

use Simpla\Contracts\ContainerInterface;
/**
 * Description of ServiceProvider
 * 
 * @package Simpla
 * @subpackage Container
 * @author robert <robert.di.jesus@gmail.com>
 * @version 1.0.0
 */   
interface ServiceProviderInterface
{ 
    
    public function register(ContainerInterface $container);
      
}
