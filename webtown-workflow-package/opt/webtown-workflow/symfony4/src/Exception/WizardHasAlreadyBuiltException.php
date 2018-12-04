<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 12:32
 */

namespace App\Exception;

use App\Wizards\BaseWizard;
use Throwable;

class WizardHasAlreadyBuiltException extends \Exception
{
    public function __construct(BaseWizard $wizard, $targetProjectPath, string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = sprintf('The `%s` wizard has already built! (Target path: `%s`)', $wizard->getDefaultName(), $targetProjectPath);
        }

        parent::__construct($message, $code, $previous);
    }
}
