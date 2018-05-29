<?php

namespace App\Tests\Configuration;

use App\Configuration\Configuration;
use App\Configuration\RecipeManager;
use App\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @param $base
     * @param $new
     * @param $result
     *
     * @dataProvider getConfigurations
     */
    public function testConfigDeepMerge($base, $new, $result)
    {
        $configuration = new Configuration(new RecipeManager(''));
        $response = $this->getMethod($configuration, 'configDeepMerge')->invokeArgs($configuration, [$base, $new]);

        $this->assertEquals($result, $response);
    }

    public function getConfigurations()
    {
//        return [
//            # 6
//            [
//                ['test' => ['subvalue' => 1, 'other' => 2]],
//                ['test' => ['subvalue' => 2]],
//                ['test' => ['subvalue' => 2, 'other' => 2]],
//            ],
//        ];
        return [
            # 0
            [
                [],
                [],
                [],
            ],
            # 1
            [
                ['test' => 1],
                [],
                ['test' => 1],
            ],
            # 2
            [
                [],
                ['test' => 2],
                ['test' => 2],
            ],
            # 3
            [
                ['test' => 1],
                ['test' => 2],
                ['test' => 2],
            ],
            # 4
            [
                ['test' => 1],
                ['test' => null],
                ['test' => null],
            ],
            # 5
            [
                ['test' => ['subvalue' => 1]],
                ['test' => null],
                ['test' => null],
            ],
            # 6
            [
                ['test' => ['subvalue' => 1, 'other' => 2]],
                ['test' => ['subvalue' => 2]],
                ['test' => ['subvalue' => 2, 'other' => 2]],
            ],
            # 7
            [
                ['test1' => 1],
                ['test2' => 2],
                [
                    'test1' => 1,
                    'test2' => 2,
                ],
            ],
            # 8
            [
                ['test' => ['subvalue' => 1, 'other' => 2]],
                ['test' => ['other2' => 2]],
                ['test' => ['subvalue' => 1, 'other' => 2, 'other2' => 2]],
            ],
            # 9
            [
                ['test' => ['subvalue' => ['test1', 'test1', 'test1']]],
                ['test' => ['subvalue' => ['test2']]],
                ['test' => ['subvalue' => ['test2']]],
            ],
        ];
    }
}
