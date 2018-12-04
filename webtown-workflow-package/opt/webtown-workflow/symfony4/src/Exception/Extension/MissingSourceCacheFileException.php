<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 18:51
 */

namespace App\Exception\Extension;

use Throwable;

class MissingSourceCacheFileException extends ExtensionException
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
