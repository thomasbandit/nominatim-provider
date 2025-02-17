<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\LocationIqNominatim;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Location;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Geocoder\Provider\LocationIqNominatim\Model\LocationIqNominatimAddress;
use Http\Client\HttpClient;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class LocationIqNominatim extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const BASE_API_URL = 'https://locationiq.org/v1';

    /**
     * @var string
     */
    private $rootUrl;

    /**
     * @var bool
     */
    private $extraTags = false;

    /**
     * @var bool
     */
    private $nameDetails = false;

    /**
     * @param HttpClient $client    an HTTP client
     * @param string     $rootUrl   Root URL of the nominatim server
     * @param string     $referer   Value of the Referer header
     */
    public function __construct(HttpClient $client, string $apiKey, $extraTags = false, $nameDetails = false)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        $this->extraTags = $extraTags;
        $this->nameDetails = $nameDetails;

        $this->rootUrl = 'https://us1.locationiq.com/v1';

        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        // if (filter_var($address, FILTER_VALIDATE_IP)) {
        //     throw new UnsupportedOperation('The Nominatim provider does not support IP addresses.');
        // }

        // $url = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $query->getLimit());
        $url = $this->rootUrl
            .'/search.php?'
            .http_build_query([
                'key' => $this->apiKey,
                'q' => $address,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => $query->getLimit(),
                'extratags' => $this->extraTags,
                'namedetails' => $this->nameDetails,
            ]);

        $content = $this->executeQuery($url, $query->getLocale());

        $json = json_decode($content);
        if (is_null($json) || !is_array($json)) {
            throw InvalidServerResponse::create($url);
        }

        if (empty($json)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json as $place) {
            $results[] = $this->jsonResultToLocation($place, false);

        }

        return new AddressCollection($results);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $url = $this->rootUrl
            .'/reverse?'
            .http_build_query([
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
                'zoom' => $query->getData('zoom', 18),
            ]);

        $content = $this->executeQuery($url, $query->getLocale());

        $json = json_decode($content);
        if (is_null($json) || isset($json->error)) {
            return new AddressCollection([]);
        }

        if (empty($json)) {
            return new AddressCollection([]);
        }

        return new AddressCollection([$this->jsonResultToLocation($json, true)]);
    }

    /**
     * @param \stdClass $place
     * @param bool      $reverse
     *
     * @return Location
     */
    private function jsonResultToLocation(\stdClass $place, bool $reverse): Location
    {
        $builder = new AddressBuilder($this->getName());

        foreach (['state', 'county'] as $i => $tagName) {
            if (isset($place->address->{$tagName})) {
                $builder->addAdminLevel($i + 1, $place->address->{$tagName}, '');
            }
        }

        // get the first postal-code when there are many
        if (isset($place->address->postcode)) {
            $postalCode = $place->address->postcode;
            if (!empty($postalCode)) {
                $postalCode = current(explode(';', $postalCode));
            }
            $builder->setPostalCode($postalCode);
        }

        $localityFields = ['city', 'town', 'village', 'hamlet'];
        foreach ($localityFields as $localityField) {
            if (isset($place->address->{$localityField})) {
                $localityFieldContent = $place->address->{$localityField};

                if (!empty($localityFieldContent)) {
                    $builder->setLocality($localityFieldContent);

                    break;
                }
            }
        }

        $builder->setStreetName($place->address->road ?? $place->address->pedestrian ?? null);
        $builder->setStreetNumber($place->address->house_number ?? null);
        $builder->setSubLocality($place->address->suburb ?? null);
        $builder->setCountry($place->address->country);
        $builder->setCountryCode(strtoupper($place->address->country_code));

        $builder->setCoordinates(floatval($place->lat), floatval($place->lon));

        $builder->setBounds($place->boundingbox[0], $place->boundingbox[2], $place->boundingbox[1], $place->boundingbox[3]);

        $location = $builder->build(LocationIqNominatimAddress::class);
        $location = $location->withAttribution($place->licence);
        $location = $location->withDisplayName($place->display_name);

        if (isset($place->osm_id)) {
            $location = $location->withOSMId(intval($place->osm_id));
        }
        if (isset($place->osm_type)) {
            $location = $location->withOSMType($place->osm_type);
        }

        if (false === $reverse) {
            $location = $location->withType($place->type);

            if (isset($place->extratags)) {
                $location = $location->withExtraTags($place->extratags);
            }

            if (isset($place->namedetails)) {
                $location = $location->withNameDetails($place->namedetails);
            }
        }

        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'locationiq';
    }

    /**
     * @param string      $url
     * @param string|null $locale
     *
     * @return string
     */
    private function executeQuery(string $url, string $locale = null): string
    {
        if (null !== $locale) {
            $url .= '&'.http_build_query([
                'accept-language' => $locale,
            ]);
        }

        $request = $this->getRequest($url);

        return $this->getParsedResponse($request);
    }
}
