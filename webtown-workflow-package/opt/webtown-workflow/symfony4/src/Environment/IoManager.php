<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 15:30
 */

namespace App\Environment;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IoManager implements EventSubscriberInterface
{
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
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'init',
        ];
    }

    public function init(ConsoleCommandEvent $event)
    {
        $this->input = $event->getInput();
        $this->output = $event->getOutput();
        $this->questionHelper = $event->getCommand()->getHelper('question');
        $this->io = new SymfonyStyle($this->input, $this->output);
    }

    public function clearScreen()
    {
//        $output->write(sprintf("\033\143"));
        $this->output->write("\n\n\n");
    }

    public function ask(Question $question)
    {
        return $this->questionHelper->ask($this->input, $this->output, $question);
    }

    public function ioAsk(Question $question)
    {
        return $this->io->askQuestion($question);
    }

    public function writeln($text)
    {
        $this->output->writeln($text);
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @return QuestionHelper
     */
    public function getQuestionHelper(): QuestionHelper
    {
        return $this->questionHelper;
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo(): SymfonyStyle
    {
        return $this->io;
    }
}