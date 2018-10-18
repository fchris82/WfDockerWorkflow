<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.08.21.
 * Time: 21:33
 */

namespace App\Exception;

/**
 * Class InvalidWfVersionException
 *
 * Ezt akkor használjuk, ammikor a WF verziója elavult, ezért frissíteni kell az adott gépen.
 */
class InvalidWfVersionException extends \Exception
{
}