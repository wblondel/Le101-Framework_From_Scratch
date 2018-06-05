<?php declare(strict_types=1);

namespace Core\HTML;

/**
 * Class Form
 * Class to generate a form easily.
 * @package Core\HTML
 */
class Form
{
    private $data;
    protected $surround = 'p';

    /**
     * Form constructor.
     * @param array $data  Data used by the form.
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Surround an HTML code with a tag.
     * @param string $html HTML code to surround.
     * @return string
     */
    protected function surround(string $html)
    {
        if (strpos($this->surround, 'div') === 0) {
            return "<{$this->surround}>{$html}</div>";
        }
        return "<{$this->surround}>{$html}</{$this->surround}>";
    }

    /**
     * @param string $index Index of the value to get.
     * @return mixed|null
     */
    protected function getValue(string $index)
    {
        if (is_object($this->data)) {
            return $this->data->$index;
        }
        return isset($this->data[$index]) ? $this->data[$index] : null;
    }

    /**
     * Return an input field.
     * @param string $name
     * @param string $label
     * @param array $options
     * @return string
     */
    public function input(string $name, string $label, array $options = [])
    {
        $id = isset($options['id']) ? $options['id'] : $name;
        $required = isset($options['required']) ? 'required' : '';
        $type = isset($options['type']) ? $options['type'] : 'text';
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        $label = '<label for="' . $id . '">' . $label . '</label>';
        $classes = '';

        if (!empty($options['class'])) {
            $classes = 'class="';
            foreach ($options['class'] as $v) {
                $classes .= $v . ' ';
            }
            $classes .= '"';
        }

        if ($type === 'textarea') {
            $input = '<textarea name="' . $name . '" ' . $classes . ' ' . $required . '>' . $this->getValue($name) . '</textarea>';
        } else {
            $input = '<input type="' . $type . '" name="' . $name . '" id="' . $id .
                '" value="' . $this->getValue($name) . '" placeholder="' . $placeholder .
                '" ' . $classes . ' ' . $required . '>';
        }
        return $this->surround($label . $input);
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
        $label = '<label>' . $label . '</label>';
        $required = isset($options['required']) ? 'required' : '';
        $classes = '';

        if (!empty($options['class'])) {
            $classes = 'class="';
            foreach ($options['class'] as $v) {
                $classes .= $v . ' ';
            }
            $classes .= '"';
        }

        $input = '<select ' . $classes . ' name="' . $name . '" ' . $required . '>';
        foreach ($choices as $k => $v) {
            $attributes = '';
            if ($k == $this->getValue($name)) {
                $attributes = ' selected';
            }
            $input .= "<option value='$k'" . $attributes . ">$v</option>";
        }
        $input .= '</select>';
        return $this->surround($label . $input);
    }

    /**
     * Return a submit button.
     * @param string $text
     * @param array $options
     * @return string
     */
    public function submit(string $text = 'Submit', array $options = [])
    {
        $classes = '';
        if (!empty($options['class'])) {
            $classes = 'class="';
            foreach ($options['class'] as $v) {
                $classes .= $v . ' ';
            }
            $classes .= '"';
        }
        return $this->surround('<button type="submit" ' . $classes . '>' . $text . '</button>');
    }
}
