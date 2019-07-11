<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 16:55
 */

namespace App\Webtown\WorkflowBundle\Tests\Twig\Extension;

use App\Webtown\WorkflowBundle\Twig\Extension\TextExtension;
use PHPUnit\Framework\TestCase;

class TextExtensionTest extends TestCase
{
    /**
     * @param string      $text
     * @param string      $result
     * @param string|null $lineChar
     *
     * @dataProvider getUnderlines
     */
    public function testUnderline($text, $result, $lineChar = null)
    {
        $extension = new TextExtension();
        $response = null === $lineChar
            ? $extension->underline($text)
            : $extension->underline($text, $lineChar)
        ;
        $this->assertEquals($result, $response);
    }

    public function getUnderlines()
    {
        return [
            ['', ''],
            ['Test', '===='],
            ['Test', '----', '-'],
            ['Árvíztűrőtükörfúrógép!', '======================'],
        ];
    }
}
