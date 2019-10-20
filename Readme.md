# Location IQ Geocode Provider 
###[(Based on Nominatim Geocoder provider)](https://github.com/geocoder-php/nominatim-provider)
[![Build Status](https://travis-ci.org/geocoder-php/nominatim-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/nominatim-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/nominatim-provider/v/stable)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/nominatim-provider/downloads)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/nominatim-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/nominatim-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/nominatim-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/nominatim-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/nominatim-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

**This is a quick fork of the [Nominatim Geocode Provider](https://github.com/geocoder-php/nominatim-provider) to use the [LocationIQ](https://locationiq.com/) service and append the `type`, `extraDetails` and `nameDetails` properties to the default Address data.**

### Install

```bash
composer require thomasbandit/locationiq-nominatim-provider
```

**Why not use the [LocationIQ Geocode Provider](https://github.com/geocoder-php/locationiq-provider)?**
* The [Nominatim Geocode Provider](https://github.com/geocoder-php/nominatim-provider) enables use of the `type` property via `withType` or `getType` functions whereas the [LocationIQ Geocode Provider](https://github.com/geocoder-php/locationiq-provider) doesn't. I went an extra step further and also provided the `extraDetails` and `nameDetails` properties; both can be separately disabled.. 

**Differences to the [LocationIQ Geocode Provider](https://github.com/geocoder-php/locationiq-provider)**
* This calls the LocationIQ requesting a JSON response than XML.
* The `type`, `extraDetails` and `nameDetails` properties have been added to the Address model.
* There are two extra arguments in the Laravel config, both are `false` by default:

```
    'providers' => [
        LocationIqNominatim::class => [
            env('LOCATION_IQ_API_KEY'),
            true, // Include extraDetails
            true, // Include nameDetails
        ],
    ],
````
