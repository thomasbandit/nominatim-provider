<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\LocationIqNominatim\Model;

use Geocoder\Model\Address;

/**
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class LocationIqNominatimAddress extends Address
{
    /**
     * @var string|null
     */
    private $attribution;

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var string|null
     */
    private $osmType;

    /**
     * @var int|null
     */
    private $osmId;

    /**
     * @var string|null
     */
    private $type;

    /**
     * $var object|null
     */
    private $extraTags;

    /**
     * $var object|null
     */
    private $nameDetails;

    /**
     * @return null|string
     */
    public function getAttribution()
    {
        return $this->attribution;
    }

    /**
     * @param null|string $attribution
     *
     * @return NominatimAddress
     */
    public function withAttribution(string $attribution = null): self
    {
        $new = clone $this;
        $new->attribution = $attribution;

        return $new;
    }

    /**
     * @deprecated
     *
     * @return null|string
     */
    public function getClass()
    {
        return $this->getCategory();
    }

    /**
     * @deprecated
     *
     * @param null|string $category
     *
     * @return NominatimAddress
     */
    public function withClass(string $category = null): self
    {
        return $this->withCategory($category);
    }

    /**
     * @return null|string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param null|string $category
     *
     * @return NominatimAddress
     */
    public function withCategory(string $category = null): self
    {
        $new = clone $this;
        $new->category = $category;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param null|string $displayName
     *
     * @return NominatimAddress
     */
    public function withDisplayName(string $displayName = null): self
    {
        $new = clone $this;
        $new->displayName = $displayName;

        return $new;
    }

    /**
     * @return null|int
     */
    public function getOSMId()
    {
        return $this->osmId;
    }

    /**
     * @param null|int $osmId
     *
     * @return NominatimAddress
     */
    public function withOSMId(int $osmId = null): self
    {
        $new = clone $this;
        $new->osmId = $osmId;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getOSMType()
    {
        return $this->osmType;
    }

    /**
     * @param null|string $osmType
     *
     * @return NominatimAddress
     */
    public function withOSMType(string $osmType = null): self
    {
        $new = clone $this;
        $new->osmType = $osmType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     *
     * @return NominatimAddress
     */
    public function withType(string $type = null): self
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getExtraTags()
    {
        return $this->extraTags;
    }

    /**
     * @param null|string $extraTags
     *
     * @return NominatimAddress
     */
    public function withExtraTags(object $extraTags = null): self
    {
        $new = clone $this;
        $new->extraTags = $extraTags;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getNameDetails()
    {
        return $this->nameDetails;
    }

    /**
     * @param null|string $nameDetails
     *
     * @return NominatimAddress
     */
    public function withNameDetails(object $nameDetails = null): self
    {
        $new = clone $this;
        $new->nameDetails = $nameDetails;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $adminLevels = [];
        foreach ($this->getAdminLevels() as $adminLevel) {
            $adminLevels[$adminLevel->getLevel()] = [
                'name' => $adminLevel->getName(),
                'code' => $adminLevel->getCode(),
                'level' => $adminLevel->getLevel(),
            ];
        }

        $lat = null;
        $lon = null;
        if (null !== $coordinates = $this->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        $countryName = null;
        $countryCode = null;
        if (null !== $country = $this->getCountry()) {
            $countryName = $country->getName();
            $countryCode = $country->getCode();
        }

        $noBounds = [
            'south' => null,
            'west' => null,
            'north' => null,
            'east' => null,
        ];

        return [
            'providedBy' => 'locationiq',
            'latitude' => $lat,
            'longitude' => $lon,
            'bounds' => null !== $this->getBounds() ? $this->getBounds()->toArray() : $noBounds,
            'streetNumber' => $this->getStreetNumber(),
            'streetName' => $this->getStreetName(),
            'postalCode' => $this->getPostalCode(),
            'locality' => $this->getLocality(),
            'subLocality' => $this->getSubLocality(),
            'adminLevels' => $adminLevels,
            'country' => $countryName,
            'countryCode' => $countryCode,
            'timezone' => $this->getTimezone(),
            'type' => $this->getType(),
            'osmType' => $this->getOSMType(),
            'extraTags' => $this->getExtraTags(),
            'nameDetails' => $this->getNameDetails(),
        ];
    }
}
