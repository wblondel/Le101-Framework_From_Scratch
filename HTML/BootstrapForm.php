<?php declare(strict_types=1);

namespace Core\HTML;

/**
 * Class BootstrapForm
 * Class to generate a BootstrapForm easily.
 * @package Core\HTML
 */
class BootstrapForm extends Form
{
    protected $surround = 'div class="form-group"';

    /**
     * Return an input field.
     * @param string $name
     * @param string $label
     * @param array $options
     * @return string
     */
    public function input(string $name, string $label, array $options = [])
    {
        $options["class"] = ['form-control'];
        return parent::input($name, $label, $options);
    }

    /**
     * Return a select field.
     * @param string $name
     * @param string $label
     * @param $choices
     * @param array $options
     * @return string
     */
    public function select(string $name, string $label, $choices, array $options = [])
    {
        $options["class"] = ['form-control'];
        return parent::select($name, $label, $choices, $options);
    }

    /**
     * Return a submit button.
     * @param string $text
     * @param array $options
     * @return string
     */
    public function submit(string $text = 'Submit', array $options = [])
    {
        $options["class"] = ['btn', 'btn-primary'];
        return parent::submit($text, $options);
    }
}
