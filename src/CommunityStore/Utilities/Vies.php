<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Client\Client as HttpClient;
use MLocati\Vies\CheckStatus;
use MLocati\Vies\CheckVat;
use MLocati\Vies\Client as ViesClient;
use Punic\Territory;
use Punic\Comparer;
use Concrete\Core\Cache\Level\ExpensiveCache;
use MLocati\Vies\CountryCodes;
use Psr\Log\LoggerInterface;
use Throwable;

class Vies
{
    private Repository $config;

    private ExpensiveCache $cache;

    private HttpClient $httpClient;

    private LoggerInterface $logger;

    private ?ViesClient $viesClient = null;

    private ?array $applicableCountryCodes = null;

    public function __construct(Repository $config, ExpensiveCache $cache, HttpClient $httpClient, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('community_store::vies.enabled');
    }

    public function setEnabled(bool $value): void
    {
        $this->config->set('community_store::vies.enabled', $value);
        $this->config->save('community_store::vies.enabled', $value);
    }

    /**
     * @param string|null $iso3166CountryCode
     * @param string|null $vatNumber
     * @param int|null $flags the flags to be passed to CountryCodes::viesToIso3166()
     *
     * @return bool|null true if $vatNumber is valid; false if $vatNumber is not valid; null in case of unapplicable countries, invalid parameters, or errors.
     */
    public function isValid(?string $iso3166CountryCode, ?string $vatNumber, ?int $flags = 0): ?bool
    {
        $iso3166CountryCode = trim((string) $iso3166CountryCode);
        if ($iso3166CountryCode === '') {
            return null;
        }
        $vatNumber = trim((string) $vatNumber);
        if ($vatNumber === '') {
            return null;
        }
        try {
            $viesCountryCode = CountryCodes::iso3166ToVies($iso3166CountryCode, $flags);
            if ($viesCountryCode === '' || !in_array($viesCountryCode, $this->getApplicableViesCountryCodes(), true)) {
                return null;
            }
            $request = new CheckVat\Request($viesCountryCode);
            $vatNumbers = [$vatNumber];
            $match = null;
            if (preg_match('/^' . preg_quote($iso3166CountryCode, '/') . '-?(.+)$/', $vatNumber, $match)) {
                array_unshift($vatNumbers, $match[1]);
            }
            $response = null;
            while (count($vatNumbers) > 1) {
                $vatNumber = array_pop($vatNumbers);
                try {
                    $request->setVatNumber($vatNumber);
                    $response = $this->getViesClient()->checkVatNumber($request);
                    if ($response->isValid() === true) {
                        break;
                    }
                } catch (Throwable $_) {
                }
                $response = null;
            }
            if ($response === null) {
                $request->setVatNumber($vatNumbers[0]);
                $response = $this->getViesClient()->checkVatNumber($request);
            }

            return $response->isValid();
        } catch (Throwable $x) {
            try {
                $this->logger->error((string) $x);
            } catch (Throwable $_) {
            }
            return null;
        }
    }

    public function getCountryStatuses(?bool &$vowAvailable = null): array
    {
        $status = $this->fetchViesStatus();
        $vowAvailable = $status->getVowStatus()->isAvailable();
        $result = [];
        foreach ($status->getCountryCodes() as $countryCode) {
            $countryStatus = $status->getCountryStatus($countryCode);
            switch ($countryCode) {
                case CountryCodes::VIES_NOTHERNIRELAND:
                    $coutryName = Territory::getName('gbnir');
                    break;
                default:
                    $coutryName = Territory::getName(CountryCodes::viesToIso3166($countryCode));
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

    /**
     * @return string[]
     */
    private function getApplicableViesCountryCodes(): array
    {
        if ($this->applicableCountryCodes === null) {
            $cacheItem = $this->cache->getItem('cs.vies.applicable_countries');
            if ($cacheItem->isHit()) {
                $applicableCountryCodes = $cacheItem->get();
                if (is_array($applicableCountryCodes)) {
                    return $this->applicableCountryCodes = $applicableCountryCodes;
                }
            }
            $this->fetchViesStatus();
        }

        return $this->applicableCountryCodes;
    }

    private function getViesClient(): ViesClient
    {
        if ($this->viesClient === null) {
            if ($this->httpClient instanceof \GuzzleHttp\Client) {
                $adapter = new \MLocati\Vies\Http\Adapter\Guzzle($this->httpClient);
            } else {
                $adapter = new \MLocati\Vies\Http\Adapter\Zend($this->httpClient);
            }
            $this->viesClient = new ViesClient($adapter);
        }

        return $this->viesClient;
    }

    private function fetchViesStatus(): CheckStatus\Response
    {
        $status = $this->getViesClient()->checkStatus();
        $this->applicableCountryCodes = $status->getCountryCodes();
        $cacheItem = $this->cache->getItem('cs.vies.applicable_countries');
        $cacheItem
            ->expiresAfter((int) $this->config->get('community_store::vies.applicableCountries.cacheLifetime'))
            ->set($this->applicableCountryCodes)
        ;
        $this->cache->save($cacheItem);

        return $status;
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
