<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.27.
 * Time: 20:07
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy;

use App\Webtown\WorkflowBundle\Test\Dummy\Filesystem;
use App\Webtown\WorkflowBundle\Tests\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FilesystemTest extends TestCase
{
    protected $appCacheContent = <<<EOS
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

class AppCache extends HttpCache
{
}

EOS;

    /**
     * @param $directory
     * @param $file
     * @param $result
     *
     * @dataProvider getExists
     */
    public function testExists($directory, $file, $result)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        $response = $filesystem->exists($file);
        $this->assertEquals($result, $response);
    }

    public function getExists()
    {
        return [
            ['test1', '/composer.json', true],
            ['test1', '/var/.gitkeep', true],
            ['test1', '/var/.git', false],
            // Directory
            ['test1', '/var', true],
        ];
    }

    /**
     * @param $directory
     * @param $file
     * @param $fileContent
     *
     * @dataProvider getDumpFiles
     */
    public function testDumpFile($directory, $file, $fileContent)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        $filesystem->dumpFile($file, $fileContent);
        $this->assertTrue($filesystem->exists($file));
        $contents = $filesystem->getContents();
        $this->assertEquals($fileContent, $contents[$file]);
    }

    public function getDumpFiles()
    {
        return [
            ['test1', '/composer.json', ''],
            ['test1', '/.gitignore', '*.iml'],
        ];
    }

    /**
     * @param $directory
     * @param $file
     * @param $append
     * @param $result
     *
     * @dataProvider getAppendToFiles
     */
    public function testAppendToFile($directory, $file, $append, $result)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        $filesystem->appendToFile($file, $append);
        $this->assertTrue($filesystem->exists($file));
        $contents = $filesystem->getContents();
        $this->assertEquals($result, $contents[$file]);
    }

    public function getAppendToFiles()
    {
        return [
            ['test1', '/.gitignore', '*.iml', '*.iml'],
            ['test1', '/var/.gitkeep', 'Teszt', 'Teszt'],
            ['test1', '/app/AppCache.php', 'Teszt', $this->appCacheContent . 'Teszt'],
        ];
    }

    /**
     * @param $directory
     * @param $file
     * @param $testFile
     * @param $fileContent
     *
     * @dataProvider getTouches
     */
    public function testTouch($directory, $file, $testFile, $fileContent)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        $filesystem->touch($file);
        $this->assertTrue($filesystem->exists($testFile));
        $contents = $filesystem->getContents();
        $this->assertEquals($fileContent, $contents[$testFile]);
    }

    public function getTouches()
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            '',
        ]);

        return [
            ['test1', '/.gitignore', '/.gitignore', ''],
            ['test1', '/app/AppCache.php', '/app/AppCache.php', $this->appCacheContent],
            // Testing alias
            ['test1', $path . 'test1/.gitignore', '/.gitignore', ''],
            ['test1', $path . 'test1/app/AppCache.php', '/app/AppCache.php', $this->appCacheContent],
        ];
    }

    /**
     * @param $directory
     * @param $origin
     * @param $target
     * @param $overwrite
     * @param $checkOrigin
     * @param $checkTarget
     * @param $fileContent
     *
     * @dataProvider getCopies
     */
    public function testCopy($directory, $origin, $target, $overwrite, $checkOrigin, $checkTarget, $fileContent)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        if ($fileContent instanceof \Exception) {
            $this->expectException(\get_class($fileContent));
        }
        $filesystem->copy($origin, $target, $overwrite);
        if (!$fileContent instanceof \Exception) {
            // Use alias
            $origin = str_replace($path, '', $origin);
            $this->assertTrue($filesystem->exists($checkOrigin ?: $origin));
            $target = str_replace($path, '', $target);
            $this->assertTrue($filesystem->exists($checkTarget ?: $target));

            $contents = $filesystem->getContents();
            $this->assertEquals($fileContent, $contents[$checkOrigin ?: $target]);
        }
    }

    public function getCopies()
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            '',
        ]);

        return [
            [
                'test1',
                '/.gitignore',
                '/.gitignore2',
                true,
                null,
                null,
                new FileNotFoundException(),
            ],
            [
                'test1',
                '/var/.git',
                '/.gitignore2',
                true,
                null,
                null,
                new FileNotFoundException(),
            ],
            [
                'test1',
                '/app/AppCache.php',
                '/app/AppCache2.php',
                true,
                null,
                null,
                $this->appCacheContent,
            ],
            [
                'test1',
                '/var/.gitkeep',
                '/app/AppCache.php',
                false,
                null,
                null,
                $this->appCacheContent,
            ],
            [
                'test1',
                '/var/.gitkeep',
                '/app/AppCache.php',
                true,
                null,
                null,
                '',
            ],
            // Directory copy
            [
                'test1',
                '/var',
                '/var2',
                true,
                '/var/.gitkeep',
                '/var2/.gitkeep',
                '',
            ],
            [
                'test1',
                '/app',
                '/var',
                true,
                '/app/AppCache.php',
                '/var/AppCache.php',
                $this->appCacheContent,
            ],
            // Testing alias
            [
                'test1',
                $path . 'test1/.gitignore',
                $path . 'test1/.gitignore2',
                true,
                null,
                null,
                new FileNotFoundException(),
            ],
            [
                'test1',
                $path . 'test1/app/AppCache.php',
                $path . 'test1/app/AppCache2.php',
                true,
                null,
                null,
                $this->appCacheContent,
            ],
            [
                'test1',
                $path . 'test1/var/.gitkeep',
                $path . 'test1/app/AppCache.php',
                false,
                null,
                null,
                $this->appCacheContent,
            ],
            [
                'test1',
                $path . 'test1/var/.gitkeep',
                $path . 'test1/app/AppCache.php',
                true,
                null,
                null,
                '',
            ],
        ];
    }

    /**
     * @param $directory
     * @param $origin
     * @param $target
     * @param $overwrite
     * @param $checkOrigin
     * @param $checkTarget
     * @param $fileContent
     *
     * @dataProvider getCopies
     */
    public function testRename($directory, $origin, $target, $overwrite, $checkOrigin, $checkTarget, $fileContent)
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            __DIR__,
            'Resources',
            $directory,
        ]);
        $filesystem = new Filesystem($path, '');

        if ($fileContent instanceof \Exception) {
            $this->expectException(\get_class($fileContent));
        }
        $filesystem->rename($origin, $target, $overwrite);
        // Use alias
        $origin = str_replace($path, '', $origin);
        $this->assertFalse($filesystem->exists($checkOrigin ?: $origin));
        $target = str_replace($path, '', $target);
        $this->assertTrue($filesystem->exists($checkTarget ?: $target));

        if (!$fileContent instanceof \Exception) {
            $contents = $filesystem->getContents();
            $this->assertEquals($fileContent, $contents[$checkTarget ?: $target]);
        }
    }
}
