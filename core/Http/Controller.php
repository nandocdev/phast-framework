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

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::jsonResponse($data, $status);
    }

    protected function html(string $html, int $status = 200): Response
    {
        return Response::htmlResponse($html, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirectResponse($url, $status);
    }

    protected function view(string $template, array $data = []): Response
    {
        $content = $this->renderTemplate($template, $data);
        return $this->html($content);
    }

    protected function validate(Request $request, array $rules): array
    {
        $validator = app(\Phast\Core\Validation\ValidatorInterface::class);
        return $validator->validate($request->getInput(), $rules);
    }

    private function renderTemplate(string $template, array $data): string
    {
        $templatePath = $this->getTemplatePath($template);
        
        if (!file_exists($templatePath)) {
            throw new \InvalidArgumentException("Template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private function getTemplatePath(string $template): string
    {
        $basePath = config('view.path', PHAST_BASE_PATH . '/resources/views');
        $template = str_replace('.', '/', $template);
        return $basePath . '/' . $template . '.php';
    }
}
