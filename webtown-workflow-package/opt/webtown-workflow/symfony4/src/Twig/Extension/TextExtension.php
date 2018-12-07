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
            new \Twig_SimpleFilter('base64', 'base64_encode')
        ];
    }
}
