<?php
/**
 * @package     phast/core
 * @subpackage  View
 * @file        ViewInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description View engine interface
 */

declare(strict_types=1);

namespace Phast\Core\View;

interface ViewInterface {
   /**
    * Render a view template
    *
    * @param string $template Template name (without extension)
    * @param array $data Data to pass to the template
    * @param string $layout Layout to use (optional, Plates handles layouts automatically)
    * @return string Rendered HTML
    */
   public function render(string $template, array $data = [], string $layout = ''): string;

   /**
    * Check if a template exists
    *
    * @param string $template Template name
    * @return bool
    */
   public function exists(string $template): bool;

   /**
    * Add global data available to all templates
    *
    * @param string $key Data key
    * @param mixed $value Data value
    * @return void
    */
   public function addGlobalData(string $key, mixed $value): void;

   /**
    * Add global data from array
    *
    * @param array $data Array of data
    * @return void
    */
   public function addGlobalDataArray(array $data): void;

   /**
    * Register a view composer for specific templates
    *
    * @param string|array $templates Template name(s)
    * @param callable $callback Callback to execute
    * @return void
    */
   public function composer(string|array $templates, callable $callback): void;
}
