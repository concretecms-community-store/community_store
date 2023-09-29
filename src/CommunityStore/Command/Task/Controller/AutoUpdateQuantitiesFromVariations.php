<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Runner\CommandTaskRunner;
use Concrete\Package\CommunityStore\Src\CommunityStore\Command\AutoUpdateQuantitiesFromVariationsCommand;
use Concrete\Core\Command\Task\Input\Definition\Definition;
use Concrete\Core\Command\Task\Input\Definition\BooleanField;

class AutoUpdateQuantitiesFromVariations extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\ControllerInterface::getName()
     */
    public function getName(): string
    {
        return t('Automatic Product Quantity Updater');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\ControllerInterface::getDescription()
     */
    public function getDescription(): string
    {
        return t('Update the product quantities from variations.');
    }
    
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Command\Task\Controller\AbstractController::getInputDefinition()
     */
    public function getInputDefinition(): ?Definition
    {
        $definition = new Definition();
        $definition->addField(new BooleanField('force', t('Force'), t('Force the execution even if automatic product quantities is disabled.')));

        return $definition;
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        $command = new AutoUpdateQuantitiesFromVariationsCommand();
        $force = $input->getField('force');
        $command->setForce($force && !empty($force->getValue()));
        
        return new CommandTaskRunner($task, $command, t('Product quantities updated succesfully.'));
    }
}
