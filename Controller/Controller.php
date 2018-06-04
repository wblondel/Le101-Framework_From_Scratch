<?php declare(strict_types=1);

namespace Core\Controller;

/**
 * Class Controller
 * @package Core\Controller
 */
class Controller
{
    protected $viewPath;
    protected $template;

    /**
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        $this->notFound();
    }

    /**
     * Render a view:
     * @param string $view
     * @param array $variables
     */
    protected function render(string $view, $variables = [])
    {
        ob_start();
        extract($variables);
        require($this->viewPath . str_replace('.', '/', $view) . '.php');
        $content = ob_get_clean();
        require($this->viewPath . 'templates/' . $this->template . '.php');
    }

    /**
     * Return a 403 Forbidden error.
     */
    protected function forbidden()
    {
        header('HTTP/1.0 403 Forbidden');
        die('403 Forbidden');
    }

    /**
     * Return a 404 Not Found error.
     */
    protected function notFound()
    {
        header('HTTP/1.0 404 Not Found');
        die('404 Not Found');
    }

    /**
     * Return a 400 Bad Request error.
     */
    protected function badRequest()
    {
        header('HTTP/1.0 400 Bad Request');
        die('400 Bad Request');
    }

    /**
     * Redirect to a Controller->action.
     * @param string $controller
     * @param string $action
     * @param string|null $mode
     */
    protected function redirect(string $controller, string $action, string $mode = null)
    {
        $location = 'index.php?p=';

        if (!is_null($mode)) {
            $location .= $mode . '.';
        }

        $location .= $controller . '.';
        $location .= $action;
        header('Location: ' . $location);
    }
}
