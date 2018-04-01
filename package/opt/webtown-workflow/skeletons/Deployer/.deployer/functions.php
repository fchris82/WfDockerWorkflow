<?php
namespace Deployer;

function sf($cmd, $options = '', $runOptions = [])
{
    return run(buildSfCommand($cmd, $options), $runOptions);
}

function buildSfCommand($cmd, $options = '')
{
    return sprintf('{{bin/php}} {{bin/console}} %s {{console_options}} %s', $cmd, $options);
}
