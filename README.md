# Ensemble Plugin
Adds a public endpoint to your application that [Ensemble](https://ens.emble.app)
can periodically request for information about your Composer packages.

🙏 Please consider [sponsoring](https://github.com/sponsors/simonhamp) the development of Ensemble 💚

## Requirements

- An Ensemble account (completely free!)
- Laravel 7+
- PHP 7+

#### Security, Privacy & Performance
To protect your application, we encrypt the information about your packages
using a unique, private key that is given to you when you set up your app in Ensemble.

This means, even if your app is only accessible via HTTP, it will be very hard for
a third party to discover what packages it depends on.

**!!! DON'T SHARE YOUR PRIVATE KEY !!!**

If you feel that the key is compromised, you will be able to generate a new one
easily.

Also, even though the endpoint is public, it requires a special kind of `POST`
containing an encrypted payload (also using the pre-shared private key), to make sure
only Ensemble can request the encrypted data about your packages.

And if someone does discover the payload, it has a time limit so it can only be used
for a short time (usually less than a minute). 

Further, to stop even Ensemble causing you problems, this plugin caches the response
before sending it back. This cache lasts for 60 minutes by default (configurable, see below).
This helps prevent Ensemble from abusing your app/server resources, either inadvertently
or in the unlikely event of a security breach.

If you disable Ensemble or we have any problems communicating with your app multiple times
in a row, we'll stop trying until you tell us otherwise.

## Installation
```
$ composer require simonhamp/ensemble-plugin
```

This will install the latest version of the plugin. You can install earlier versions that will support Laravel 5.5+, but I highly recommend that you upgrade your app to the latest version of Laravel.

**NB: This package currently only supports Laravel.**
If you'd like to use Ensemble with another framework, please
[raise an issue](https://github.com/simonhamp/ensemble-plugin/issues/new?template=integration.md)

##### Configure
Add the following to your `.env`:

```
# Required config
ENSEMBLE_ENABLED=true
ENSEMBLE_PRIVATE_KEY=#The key provided when creating your app in Ensemble#

# Optional config
ENSEMBLE_ENDPOINT=#The URL we'll use to communicate with your app. Default: /ensemble#
ENSEMBLE_CACHE_TTL=#The cache life in minute. Default: 60#
```
