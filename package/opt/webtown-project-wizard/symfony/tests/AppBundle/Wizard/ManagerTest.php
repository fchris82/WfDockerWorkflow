<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 20:47
 */

namespace Tests\AppBundle\Skeleton;

use AppBundle\Wizard\Manager;
use AppBundle\Wizard\PublicWizardInterface;
use AppBundle\Wizard\WizardInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerTest extends TestCase
{
    const GROUP_NAME = 'testgroup';

    /**
     * @param array $wizardsConfig
     * @param array $result
     *
     * @dataProvider getConfigs
     */
    public function testGetSkeletons(array $wizardsConfig, array $result)
    {
        $manager = new Manager();
        foreach ($wizardsConfig as $config) {
            $manager->addWizard($config[0], self::GROUP_NAME, $config[1]);
        }

        $response = $manager->getWizards();
        $this->assertEquals($result, count($response) ? array_values($response[self::GROUP_NAME]) : $response);
    }

    public function getConfigs()
    {
        $wizard1 = new Wizard('wizard1');
        $wizard2 = new Wizard('wizard2');
        $wizard3 = new Wizard('wizard3');
        $wizard4 = new Wizard('wizard4');

        $config1 = [$wizard1, 0];
        $config2 = [$wizard2, 0];
        $config3 = [$wizard3, 10];
        $config4 = [$wizard4, -10];

        return [
            [[], []],
            [[$config1], [$wizard1]],
            [[$config1, $config2], [$wizard1, $wizard2]],
            [[$config2, $config1], [$wizard2, $wizard1]],
            [[$config1, $config3], [$wizard3, $wizard1]],
            [[$config4, $config3], [$wizard3, $wizard4]],
            [[$config1, $config2, $config3, $config4], [$wizard3, $wizard1, $wizard2, $wizard4]],
        ];
    }
}

class Wizard implements WizardInterface, PublicWizardInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getInfo()
    {
        // TODO: Implement getInfo() method.
    }

    public function isBuilt($targetProjectDirectory)
    {
        // TODO: Implement isBuilt() method.
    }

    public function build($targetProjectDirectory)
    {
        // TODO: Implement build() method.
    }

    /**
     * @param InputInterface $input
     * @return WizardInterface
     */
    public function setInput(InputInterface $input)
    {
        // TODO: Implement setInput() method.
    }

    /**
     * @param OutputInterface $output
     * @return WizardInterface
     */
    public function setOutput(OutputInterface $output)
    {
        // TODO: Implement setOutput() method.
    }

    /**
     * @param Command $command
     * @return WizardInterface
     */
    public function setCommand(Command $command)
    {
        // TODO: Implement setCommand() method.
    }

    /**
     * 'dev' => [... dev packages ...]
     * 'nodev' => [... nodev packages ...]
     *
     * Eg:
     * <code>
     *  return ['dev' => ["friendsofphp/php-cs-fixer:~2.3.3"]];
     * </code>
     *
     * @return array
     */
    public function getComposerPackages()
    {
        // TODO: Implement getComposerPackages() method.
    }
}
