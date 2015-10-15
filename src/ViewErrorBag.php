<?php

namespace Seaston\LaravelErrors;

use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag as BaseViewErrorBag;

class ViewErrorBag extends BaseViewErrorBag
{
    // Default Classes
    protected $classes = [
        'field'        => 'error-field',
        'list'         => 'error-list',
        'fieldList'    => 'error-fieldList',
        'with-message' => 'has-message'
    ];

    // Default messages
    protected $messages = [
        'list' => 'There was a problem with your input.'
    ];

    // List message for use with render()
    protected $listMessage;

    /**
     * Load an existing Illuminate\Support\ViewErrorBag
     *
     * @param  Illuminate\Http\Request $request
     * @return void
     */
    public function make(Request $request)
    {
        if ($errors = $request->session()->get('errors')) {
            $this->bags = $errors->getBags();
        }
    }

    /**
     * Has any of the passed keys
     *
     * @param  mixed  $key
     * @return boolean
     */
    public function has($key = null)
    {
        if (func_num_args() > 1) {
            return $this->has(func_get_args());
        }

        if (is_array($key)) {
            return (bool) array_filter($key, function ($value) {
                return $this->has($value);
            });
        }

        return $this->first($key) !== '';
    }

    /**
     * Alias of has()
     *
     * @return boolean
     */
    public function hasAny()
    {
        return $this->has(func_get_args());
    }

    /**
     * Has all of the passed keys
     *
     * @param  mixed  $key
     * @return boolean
     */
    public function hasAll($key = null)
    {
        if (func_num_args() > 1) {
            return $this->hasAll(func_get_args());
        }

        $args = (array) $key;

        return count($args) == count(array_filter($args, function ($value) {
            return $this->has($value);
        }));
    }

    /**
     * Return HTML class parameter
     *
     * @param  string  $key
     * @param  mixed  $classes
     * @param  boolean $single
     * @return string
     */
    public function classes($key, $classes = null, $single = false)
    {
        $classes = $classes ?: $this->getClass('field');
        $classes = is_array($classes) ? $classes : explode(' ', $classes);
        $errorClass = array_shift($classes);

        if ($this->has($key)) {
            $errorClasses = preg_replace('/\|/', ' ', $errorClass);
            array_unshift($classes, $errorClasses);
        }

        if (!$classes) {
            return false;
        }

        $classes = implode(' ', $classes);

        return $single ? " $classes" : " class=\"$classes\"";
    }

    /**
     * Return a string of classes
     *
     * @param  string $key
     * @param  mixed $classes
     * @return string
     */
    public function singleClass($key, $classes = null)
    {
        $classes = $classes ?: $this->getClass('field');
        return $this->classes($key, $classes, true);
    }

    /**
     * Render an unordered list
     *
     * @param  string $key
     * @param  string $class
     * @return string
     */
    public function render($key = null, $class = null)
    {
        $class = $class ?: $this->getClass('list');

        if (! $this->all() || ($key && ! $this->has($key))) {
            return false;
        }

        if ($key) {
            return $this->lister($this->get($key), $class);
        }

        return $this->lister($this->all(), $class);
    }

    /**
     * Render a list of messages for an individual field
     *
     * @param  string $key
     * @param  string|null $class
     * @return string
     */
    public function field($key, $class = null)
    {
        $class = $class ?: $this->getClass('fieldList');
        return $this->render($key, $class);
    }

    public function withMessage($message = true)
    {
        $this->setListMessage($message);
        return $this;
    }

    /**
     * Generate unordered list markup
     *
     * @param  array $errors
     * @param  mixed $class
     * @return string
     */
    protected function lister(array $errors, $class)
    {
        if (! $errors) {
            return false;
        }

        $class = ! is_array($class) ? array($class) : $class;

        $message = $this->getListMessage($errors);

        if ($message) {
            $class[] = $this->getClass('with-message');
        }

        $class = $class ? ' class="' . implode(' ', $class) .'"' : '';

        $list = '<div'.$class.'>';

        if ($message) {
            $list .= '<p>' . $message . '</p>';
        }

        $list .= '<ul>';

        foreach ($errors as $error) {
            $list .= '<li>' . $error . '</li>';
        }

        $list .= '</ul>';
        $list .= '</div>';

        return $list;
    }

    /**
     * Return a individual class
     *
     * @param  string $key
     * @return string|null
     */
    public function getClass($key)
    {
        return array_key_exists($key, $this->classes)
                ? $this->classes[$key]
                : null;
    }

    /**
     * Set the default classes
     *
     * @param array $classes
     */
    public function setClasses(array $classes)
    {
        $this->classes = array_merge($this->classes, $classes);
    }

    /**
     * Set an individual default class
     *
     * @param string $key
     * @param string $value
     */
    public function setClass($key, $value)
    {
        $this->setClasses([$key => $value]);
    }

    /**
     * Return a individual class
     *
     * @param  string $key
     * @return string|null
     */
    public function getMessage($key)
    {
        return array_key_exists($key, $this->messages)
                ? $this->messages[$key]
                : null;
    }

    /**
     * Set the default messages
     *
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * Set an individual default message
     *
     * @param string $key
     * @param string $value
     */
    public function setMessage($key, $value)
    {
        $this->setMessages([$key => $value]);
    }

    /**
     * Set the message for a rendered list
     *
     * @param mixed $message
     */
    protected function setListMessage($message)
    {
        if ($message) {
            $this->listMessage = $message === true ? $this->getMessage('list') : $message;
        }
    }

    /**
     * Get and reset the message for a rendered list
     *
     * @param  array  $errors
     * @return string
     */
    protected function getListMessage(array $errors)
    {
        $message = $this->listMessage;

        if ($message) {
            $this->listMessage = null;
        }

        return $this->renderListMessage($message, $errors);
    }

    /**
     * Render the message for a rendered list and transform
     * the message based on the number of errors
     *
     * @param  mixed $message
     * @param  array  $errors
     * @return string
     */
    protected function renderListMessage($message, array $errors)
    {
        // If there is no message or the message has no multiples separator return it
        if (! $message || ! str_contains($message, '|')) return $message;

        // Get the single and multiple versions of the string
        list($single, $many) = explode('|', $message);

        // Return the correct message based on the number of errors
        return count($errors) == 1 ? $single : $many;
    }
}
