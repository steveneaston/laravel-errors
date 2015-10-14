<?php

namespace Seaston\LaravelErrors;

use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag as BaseViewErrorBag;

class ViewErrorBag extends BaseViewErrorBag
{

    protected $classes = [
        'field' => 'field-error',
        'list'  => 'error-desc',
        'message' => 'error'
    ];

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
    public function message($key, $class = null)
    {
        $class = $class ?: $this->getClass('message');
        return $this->render($key, $this->getClass('message'));
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
        $class = $class ? ' class="' . implode(' ', $class) .'"' : '';

        $list = '<ul'.$class.'>';

        foreach ($errors as $error) {
            $list .= '<li>' . $error . '</li>';
        }

        $list .= '</ul>';

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
}
