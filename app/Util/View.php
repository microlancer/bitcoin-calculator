<?php
namespace App\Util;

class View
{
    private $vars = [];

    public function addVars(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    public function render($view, $vars = [])
    {
        $vars = array_merge($this->vars, $vars);
        include __DIR__ . '/../View/' . $view . '.phtml';
    }
}
