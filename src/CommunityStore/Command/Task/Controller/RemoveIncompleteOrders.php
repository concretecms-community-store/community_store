<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Runner\CommandTaskRunner;
use Concrete\Core\Command\Task\Input\Definition\Definition;
use Concrete\Core\Command\Task\Input\Definition\Field;
use Concrete\Package\CommunityStore\Src\CommunityStore\Command\RemoveIncompleteOrdersCommand;

class RemoveIncompleteOrders extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\ControllerInterface::getName()
     */
    public function getName(): string
    {
        return t('Remove Incomplete Orders');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\ControllerInterface::getDescription()
     */
    public function getDescription(): string
    {
        return t('Remove older incomplete orders (assists with GDPR compliance).');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\AbstractController::getInputDefinition()
     */
    public function getInputDefinition(): ?Definition
    {
        $definition = new Definition();
        $definition->addField(new Field('number_days', t('Age of incomplete order in days'), t('The minimum age in days of incomplete orders to remove.')));

        return $definition;
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        $command = new RemoveIncompleteOrdersCommand();
        $numberOfDays = $input->getField('number_days');
        $numberOfDays = (int)$numberOfDays->getValue();

        if (!is_numeric($numberOfDays) || $numberOfDays < 0) {
            $numberOfDays = 7;
        }

        $command->setDays($numberOfDays);
        return new CommandTaskRunner($task, $command, t('Incomplete orders removed.'));
    }
}
