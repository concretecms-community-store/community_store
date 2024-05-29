<?php

declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Localization\Service\Date;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogEntry;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogProviderFactory;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die('Access Denied.');

class Payments extends DashboardPageController
{
    public function view(): void
    {
        $this->addHeaderItem(<<<'EOT'
<style>
[v-cloak] {
    display: none;
}
</style>
EOT
        );
        $this->requireAsset('javascript', 'vue');
        if (!$this->app->make(LogProviderFactory::class)->hasRegisteredProviders()) {
            $this->error->add(t('No payment method is providing log entries'));
        }
        $this->set('localization', $this->app->make(Localization::class));
        $this->set('urlResolver', $this->app->make(ResolverManagerInterface::class));
        $date = (string) $this->request->query->get('date', '');
        $this->set('date', preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '');
        $orderID = (int) $this->request->query->get('orderID', '0');
        $this->set('orderID', $orderID > 0 ? $orderID : null);
    }

    public function fetch(): JsonResponse
    {
        if (!$this->token->validate('cs-pm-log')) {
            throw new UserMessageException($this->token->getErrorMessage());
        }
        $logProviders = $this->app->make(LogProviderFactory::class)->getRegisteredProviders();
        if ($logProviders === []) {
            throw new UserMessageException(t('No payment method is providing log entries'));
        }
        $listBy = $this->request->request->get('listBy');
        $result = [];
        switch ($listBy) {
            case 'date':
                $date = $this->request->request->get('date');
                if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    throw new UserMessageException(t('Invalid parameter: %s', 'date'));
                }
                [$fromInclusive, $toExclusive] = $this->expandDate($date); 
                foreach ($logProviders as $logProvider) {
                    $result = array_merge($result, $logProvider->findByDate($fromInclusive, $toExclusive));
                }
                $sort = 1;
                break;
            case 'orderID':
                $orderID = $this->request->request->get('orderID');
                $orderID = is_numeric($orderID) ? (int) $orderID : 0;
                if ($orderID <= 0) {
                    throw new UserMessageException(t('Invalid parameter: %s', 'orderID'));
                }
                foreach ($logProviders as $logProvider) {
                    $result = array_merge($result, $logProvider->findByOrderID($orderID));
                }
                $sort = 1;
                break;
            default:
                throw new UserMessageException(t('Invalid parameter: %s', 'listBy'));
        }
        usort(
            $result,
            static function (LogEntry $a, LogEntry $b) use ($sort): int
            {
                if ($a->dateTime < $b->dateTime) {
                    return -$sort;
                }
                if ($a->dateTime > $b->dateTime) {
                    return $sort;
                }
                return 0;
            }
        );

        return $this->app->make(ResponseFactoryInterface::class)->json($result);
    }

    private function expandDate(string $date): array
    {
        $dateService = $this->app->make(Date::class);
        $systemTimezone = $dateService->getTimezone('system');
        $userTimezone = $dateService->getTimezone('user');
        $fromUserTime = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $userTimezone);
        $fromSystemTime = $fromUserTime->setTimezone($systemTimezone);
        $toSystemTime = $fromSystemTime->add(new DateInterval('P1D'));

        return [$fromSystemTime, $toSystemTime];
    }
}
