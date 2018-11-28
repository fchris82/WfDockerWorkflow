<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:30
 */

namespace App\Exception;

use Wizards\BaseWizard;

class WizardWfIsRequiredException extends WizardSomethingIsRequiredException
{
    public function __construct(BaseWizard $wizard, $targetProjectPath, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        if (!$message) {
            $message = sprintf('The `%s` wizard needs initialized and configured WF! (Target path: `%s`)', $wizard->getDefaultName(), $targetProjectPath);
        }

        parent::__construct($message, $code, $previous);
    }
}
