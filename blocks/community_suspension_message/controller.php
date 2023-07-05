<?php

namespace Concrete\Package\CommunityStore\Block\CommunitySuspensionMessage;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btTable
     */
    protected $btTable = 'btCommunitySuspensionMessage';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btDefaultSet
     */
    protected $btDefaultSet = 'community_store';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceWidth
     */
    protected $btInterfaceWidth = 450;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceHeight
     */
    protected $btInterfaceHeight = 460;

    /**
     * @var string|null
     */
    protected $cssClass;

    /**
     * @var bool|int|string|null
     */
    protected $useCustomMessage;

    /**
     * @var string|null
     */
    protected $customMessage;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeName()
     */
    public function getBlockTypeName()
    {
        return t('Suspension Message');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeDescription()
     */
    public function getBlockTypeDescription()
    {
        return t('Display a message to your customer when sales are suspended');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::save()
     */
    public function save($args)
    {
        parent::save([
            'cssClass' => isset($args['cssClass']) ? trim((string) $args['cssClass']) : '',
            'useCustomMessage' => empty($args['useCustomMessage']) ? 0 : 1,
            'customMessage' => empty($args['customMessage']) ? '' : LinkAbstractor::translateTo((string) $args['customMessage']),
        ]);
    }

    public function add()
    {
        $this->cssClass = 'alert alert-danger';
        $this->useCustomMessage = false;
        $this->customMessage = '';

        return $this->edit();
    }

    public function edit()
    {
        $this->set('editor', $this->app->make('editor'));
        $this->set('cssClass', (string) $this->cssClass);
        $this->set('useCustomMessage', (bool) $this->useCustomMessage);
        $this->set('customMessage', $this->customMessage ? LinkAbstractor::translateFromEditMode($this->customMessage) : '');
    }

    public function view()
    {
        $editMode = false;
        $page = Page::getCurrentPage();
        $editMode = $page && !$page->isError() && $page->isEditMode();
        if ($editMode) {
            $this->set('localization', $this->app->make(Localization::class));
        }
        $salesSuspension = $this->app->make(SalesSuspension::class);
        $show = $salesSuspension->salesCurrentlySuspended();
        $message = '';
        if ($show || $editMode) {
            $message = $this->useCustomMessage && $this->customMessage ? LinkAbstractor::translateFrom($this->customMessage) : '';
            if ($message === '') {
                $message = $salesSuspension->getSuspensionMessage();
            }
        }
        $this->set('editMode', $editMode);
        $this->set('show', $show);
        $this->set('message', $message);
        $this->set('cssClass', (string) $this->cssClass);
    }
}
