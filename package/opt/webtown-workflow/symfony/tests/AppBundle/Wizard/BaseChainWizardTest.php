<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 11:36
 */

namespace AppBundle\Wizard;

use PHPUnit\Framework\TestCase;

class BaseChainWizardTest extends TestCase
{
    /**
     * @param array $array1
     * @param array $array2
     * @param array $result
     *
     * @dataProvider getArrays
     */
    public function testDeepArrayMerge($array1, $array2, $result)
    {
        $wizard = new TestChainWizard();
        $response = $wizard->testDeepArrayMerge($array1, $array2);

        $this->assertEquals($result, $response);
    }

    public function getArrays()
    {
        return [
            [[], [], []],
            [[], ['test'], ['test']],
            [['test1'], ['test2'], ['test1', 'test2']],
            [
                [
                    'nodev' => [
                        'prog1',
                        'prog2',
                    ],
                    'dev' => [
                        'prog3',
                        'prog4',
                    ]
                ],
                [
                    'nodev' => [
                        'prog5'
                    ]
                ],
                [
                    'nodev' => [
                        'prog1',
                        'prog2',
                        'prog5',
                    ],
                    'dev' => [
                        'prog3',
                        'prog4',
                    ]
                ],
            ]
        ];
    }
}

class TestChainWizard extends BaseChainWizard
{
    /**
     * Only alias
     *
     * @param $array1
     * @param $array2
     * @return array
     */
    public function testDeepArrayMerge($array1, $array2)
    {
        return $this->deepArrayMerge($array1, $array2);
    }

    protected function getWizardNames()
    {
        return [];
    }
}