<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 13:11
 */

namespace App\Environment;

class EnvParser
{
    /**
     * A /package/opt/webtown-workflow/symfony/docker-compose.yml fájlban lehet átadni paramétereket, amik
     * kellhetnek majd generálásoknál. Pl ORIGINAL_PWD .
     *
     * @param string      $name
     * @param null|string $default
     *
     * @return null|string
     */
    public function get($name, $default = null)
    {
        return array_key_exists($name, $_ENV) ? $_ENV[$name] : $default;
    }
}
