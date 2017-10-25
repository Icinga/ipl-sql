<?php

namespace ipl\Html;

class FormattedString implements ValidHtml
{
    protected $escaped = true;

    /** @var ValidHtml[] */
    protected $arguments = [];

    /** @var ValidHtml */
    protected $string;

    public function __construct($string, array $arguments = [])
    {
        $this->string = Html::wantHtml($string);

        foreach ($arguments as $key => $val) {
            $this->arguments[$key] = Html::wantHtml($val);
        }
    }

    public static function create($string)
    {
        $args = func_get_args();
        return new static(array_shift($args), $args);
    }

    public function render()
    {
        return vsprintf(
            $this->string->render(),
            $this->arguments
        );
    }
}
