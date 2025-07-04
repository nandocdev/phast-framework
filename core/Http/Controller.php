<?php
/**
 * @package     phast/core
 * @subpackage  Http
 * @file        Controller
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base controller class
 */

declare(strict_types=1);

namespace Phast\Core\Http;

use Phast\Core\View\ViewInterface;

abstract class Controller {
   protected function json(mixed $data, int $status = 200): Response {
      return Response::jsonResponse($data, $status);
   }

   protected function html(string $html, int $status = 200): Response {
      return Response::htmlResponse($html, $status);
   }

   protected function redirect(string $url, int $status = 302): Response {
      return Response::redirectResponse($url, $status);
   }

   protected function view(string $template, array $data = [], string $layout = 'default'): Response {
      $viewEngine = app(ViewInterface::class);
      // Note: layout parameter is maintained for API compatibility but Plates handles layouts automatically
      $content = $viewEngine->render($template, $data);
      return $this->html($content);
   }

   protected function validate(Request $request, array $rules): array {
      $validator = app(\Phast\Core\Validation\ValidatorInterface::class);
      return $validator->validate($request->getInput(), $rules);
   }
}
