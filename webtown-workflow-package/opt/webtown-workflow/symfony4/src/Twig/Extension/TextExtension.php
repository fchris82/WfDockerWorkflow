<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.07.
 * Time: 17:00
 */

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;

class TextExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('base64', 'base64_encode'),
            new \Twig_SimpleFilter('md_underline', [$this, 'underline']),
        ];
    }

    public function underline($title, $lineChar = '=')
    {
        return str_repeat($lineChar, mb_strlen($title));
    }
}
