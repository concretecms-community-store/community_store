<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Cache\Level\ExpensiveCache;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\Client\Client as HttpClient;
use Punic\Comparer;
use Punic\Territory;
use Throwable;
use VATLib\Checker;
use VATLib\Exception as VATLibException;
use VATLib\Vies\CheckStatus;

class OnlineVATChecker
{
    const CACHE_LIFETIME = 500;

    private Repository $config;

    private ExpensiveCache $cache;

    private HttpClient $httpClient;

    private ?Checker $checker = null;

    public function __construct(Repository $config, ExpensiveCache $cache, HttpClient $httpClient)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('community_store::vat.check_online');
    }

    public function setEnabled(bool $value): void
    {
        $this->config->set('community_store::vat.check_online', $value);
        $this->config->save('community_store::vat.check_online', $value);
    }

    /**
     * @return string the normalized $vatNumber (or an empty string if it's invalid)
     */
    public function checkAndNormalize(?string $vatNumber, ?string $countryCode): string
    {
        $cacheItem = $this->cache->getItem('cs.vat.online.' . sha1($countryCode) . '_' . sha1($vatNumber));
        try {
            $check = $cacheItem->isHit() ? $cacheItem->get() : null;
        } catch (Throwable $x) {
            $check = null;
        }
        if (!$check) {
            $check = $this->getChecker()->check($vatNumber, $countryCode);
            $this->cache->save($cacheItem->set($check)->expiresAfter(self::CACHE_LIFETIME));
        }
        if ($check->isInvalid()) {
            return '';
        }
        return $check->getLongVatNumber();
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     */
    public function getCountryStatuses(?bool &$vowAvailable = null): array
    {
        try {
            $status = $this->getChecker()->getViesClient()->checkStatus();
        } catch (VATLibException $x) {
            throw new UserMessageException($x->getMessage());
        }
        $vowAvailable = $status->getVowStatus()->isAvailable();
        $result = [];
        foreach ($status->getCountryCodes() as $countryCode) {
            $countryStatus = $status->getCountryStatus($countryCode);
            switch ($countryCode) {
                case 'XI':
                    $coutryName = Territory::getName('gbnir');
                    break;
                case 'EL':
                    $coutryName = Territory::getName('GR');
                    break;
                default:
                    $coutryName = Territory::getName($countryCode);
                    break;
            }
            $result[] = [
                'countryCode' => $countryCode,
                'countryName' => $coutryName,
                'available' => $countryStatus->isAvailable(),
                'status' => $this->translateCountryAvailability($countryStatus->getAvailability()),
            ];
        }
        $cmp = new Comparer();
        usort(
            $result,
            static function (array $a, array $b) use ($cmp): int {
                return $cmp->compare($a['countryName'], $b['countryName']);
            }
        );

        return $result;
    }

    private function getChecker(): Checker
    {
        if ($this->checker === null) {
            if ($this->httpClient instanceof \GuzzleHttp\Client) {
                $adapter = new \VATLib\Http\Adapter\Guzzle($this->httpClient);
            } else {
                $adapter = new \VATLib\Http\Adapter\Zend($this->httpClient);
            }
            $checker = new Checker();
            $this->checker = $checker->setViesClient(new \VATLib\Vies\Client($adapter));
        }

        return $this->checker;
    }

    private function translateCountryAvailability(string $availability): string
    {
        switch ($availability) {
            case CheckStatus\Response\CountryStatus::AVAILABILITY_AVAILABLE:
                return t('Available');
            case CheckStatus\Response\CountryStatus::AVAILABILITY_UNAVAILABLE:
                return t('Unavailable');
            case CheckStatus\Response\CountryStatus::AVAILABILITY_MONITORING_DISABLED:
                return t('Monitoring disabled');
            default:
                return $availability;
        }
    }
}
