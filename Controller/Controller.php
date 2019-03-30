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
    protected function render(string $view, string $pageTitle = null, array $res = null, $variables = [])
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
        exit(http_response_code(403));
    }

    /**
     * Return a 404 Not Found error.
     */
    protected function notFound()
    {
        exit(http_response_code(404));
    }

    /**
     * Return a 400 Bad Request error.
     */
    protected function badRequest()
    {
        exit(http_response_code(400));
    }

    /**
     * Redirect to a Controller->action.
     * @param string $controller
     * @param string $action
     * @param string|null $mode
     */
    protected function redirect(string $controller = null, string $action = null, string $mode = null)
    {
        $location = '/';

        if (!is_null($mode)) {
            $location .= $mode . '/';
        }

        if (!is_null($controller)) {
            $location .= $controller . '/';

            if (!is_null($action)) {
                $location .= $action;
            }
        }

        header('Location: ' . $location);
        exit();
    }
}
