<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        MiddlewareInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Middleware interface for HTTP request processing
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

interface MiddlewareInterface {
   /**
    * Handle the incoming request
    * 
    * @param Request $request The incoming request
    * @param callable $next The next middleware in the chain
    * @return Response
    */
   public function handle(Request $request, callable $next): Response;
}
