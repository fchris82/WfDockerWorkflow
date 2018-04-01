<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace AppBundle\Wizard;

use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class BaseSkeleton.
 */
abstract class BaseWizard implements WizardInterface
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * Beállítjuk az $input-ot. Ez futás alatt változatlan.
     *
     * @param InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Beállítjuk az $output-ot. Ez futás alatt változatlan.
     *
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param Command $command
     *
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    protected function getQuestionHelper()
    {
        if (!$this->questionHelper) {
            $this->questionHelper = $this->command->getHelper('question');
        }

        return $this->questionHelper;
    }

    protected function ask(Question $question)
    {
        return $this->getQuestionHelper()->ask($this->input, $this->output, $question);
    }

    protected function execCmd($cmd, $output = [], $handleReturn = null)
    {
        $replace = [
            '&&' => '<question>&&</question>',
            '|' => '<question>|</question>',
        ];
        $printedCmd = str_replace(
            array_keys($replace),
            array_values($replace),
            $cmd
        );

        $this->output->writeln(sprintf('[exec] <comment>%s</comment>', $printedCmd));
        exec($cmd, $output, $return);
        if ($return == 0) {
            $this->output->writeln(sprintf('[<info>OK</info>] %s', $printedCmd));
        } else {
            $this->output->writeln(sprintf('[<error>ERROR</error> (%d)] %s', $return, $printedCmd));
        }

        if (is_callable($handleReturn)) {
            $handleReturn($return, $output);
        }

        return $output;
    }

    /**
     * A /package/opt/webtown-workflow/symfony/docker-compose.yml fájlban lehet átadni paramétereket, amik
     * kellhetnek majd generálásoknál. Pl ORIGINAL_PWD .
     *
     * @param string      $name
     * @param null|string $default
     *
     * @return null|string
     */
    protected function getEnv($name, $default = null)
    {
        return array_key_exists($name, $_ENV) ? $_ENV[$name] : $default;
    }
}
