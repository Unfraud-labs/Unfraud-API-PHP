#Unfraud API PHP

## Description ##

This package provides an API for the [Unfraud web services].

## Install via Composer ##

We recommend installing this package with [Composer](http://getcomposer.org/).

### Download Composer ###

To download Composer, run in the root directory of your project:

```bash
curl -sS https://getcomposer.org/installer | php
```

You should now have the file `composer.phar` in your project directory.

### Require Autoloader ###

After installing the dependencies, you need to require the Composer autoloader
from your code:

```php
require 'vendor/autoload.php';
```

## Usage ##

To use this API, create a new `\Unfraud\Unfraud` object. The constructor
takes your Unfraud API key, and an optional options array as
arguments. This object is immutable. You then build up the request using the
`->with*` methods as shown below. Each method call returns a new object. The
previous object is not modified.

If there is a validation error in the data passed to a `->with*` method, a
`\Unfraud\Exception` will be thrown. This validation can be disabled by
setting `validateInput` to `false` in the options array for
`\Unfraud\Unfraud`, but it is recommended that you keep it on at least
through development as it will help ensure that you are sending valid data to
the web service.

After creating the request object, send a Score request by calling `->score()`. If the request succeeds, a
model object will be returned for the endpoint. If the request fails, an
exception will be thrown.

See the API documentation for more details.


## Example

```php
<?php
require_once 'vendor/autoload.php';
use Unfraud;

# The constructor for Unfraud takes your user ID, your license key, and
# optionally an array of options.
$uf = new Unfraud('wq9d8hsa9fa87ustyk2j3h4kj9d823j4e1');

$request = $uf
        ->with([
            'type' => 'new_order',
            'user_id' => '1',
            'user_email' => 'demo@unfraud.com',
            'name' => 'Demo',
            'surname' => 'Unfraud',
            'order_id' => 'DEMO1000000154',
            'amount' => 323.21,
            'currency_code' => 'USD',
            'session_id' => session_id(),
            'ip_address' => '127.0.0.1',
            'timestamp' => time(),
        ])
        ->withBilling([
            "name" => 'First Last',
            "address_1" => '101 Address Rd.',
            "address_2" => 'Unit 5',
            "city" => 'New York',
            "region" => 'NY',
            "country" => 'US',
            "zipcode" => '10010',
            "phone" => '323-123-4321',
        ])
        ->withShipping([
            "address_1" => '322 Ship Addr. Ln.',
            "address_2" => 'St. 43',
            "city" => 'Nowhere',
            "region" => 'OK',
            "country" => 'US',
            "zipcode" => '73003',
            "phone" => '403-321-2323',
        ])
        ->withShoppingCartItem([
            "item_id" => '3241',
            "product_title" => "Product Name",
            "price" => "100.00",
            "brand" => "Brand",
            "category" => "Accessories",
            "quantity" => "2"
        ]);

# To get the Unfraud Score response model, use ->score():
$scoreResponse = $request->score();

print($scoreResponse['unfraud_score'] . "\n");

```

## Support ##

Please report all issues with this code using the
[GitHub issue tracker](https://github.com/Unfraud/Unfraud-API-PHP/issues).

If you are having an issue with the minFraud service that is not specific
to the client API, please see
[our support page](http://www.unfraud.com/en/support).

## Requirements  ##

This code requires PHP 5.4 or greater. Older versions of PHP are not
supported. This library works and is tested with HHVM.

There are several other dependencies as defined in the `composer.json` file.

## Contributing ##

Patches and pull requests are encouraged. All code should follow the PSR-2
style guidelines. Please include unit tests whenever possible.


## Copyright and License ##

This software is Copyright (c) 2016 by UnFraud.

This is free software, licensed under the Apache License, Version 2.0.