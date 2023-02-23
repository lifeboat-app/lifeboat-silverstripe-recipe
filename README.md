# Lifeboat SDK - Silverstripe Recipe

A simple wrapper for
[Lifeboat PHP SDK](https://github.com/lifeboat-app/php-sdk)

This wrapper provides access to 2 classes which your app can extend from
to make it easy to create Lifeboat Apps. It also loads
the `APP_ID` and `APP_SECRET` directly from your
environment variables.

## Installation
You can install this module via [Composer](https://getcomposer.org).
Run the command
```
composer require lifeboat/silverstripe
```

## Configuration

**.env**
```dotenv
LIFEBOAT_APP_ID='[[Your App ID goes here]]'
LIFEBOAT_APP_SECRET='[[Your App Secret goes here]]'
```

### Alternatively create a `.yml` config
**lifeboat.yml**
```yml
---
Name: lifeboat_config
After:
  - '#lifeboat_silverstripe_app'
---
Lifeboat\Models\Site:
  APP_ID: "[[YOUR APP ID]]"
  APP_SECRET: ""
---
```

<p>&nbsp;</p>

## Helper Classes

**Lifeboat\Models\Site**
```php
// Get the current active site object
Lifeboat\Models\Site::curr();
```
This object will automatically store the user's active
site and allows for automatic filtering of the objects
so that you only show objects this user has access to.

<p>&nbsp;</p>

**Lifeboat\Controllers\AppController**
```php
class YourController extends Lifeboat\Controllers\AppController {}
```
By extending from the `AppController` class 
you'll ensure that anyone that interacts with your app 
is authenticated and is using the correct site object

<p>&nbsp;</p>

**Lifeboat\Extensions\SiteObject**
```yml
YourObject:
  extensions:
    - Lifeboat\Extensions\SiteObject
```
Make sure to add the `SiteObject` extension to your
Data Models. This will add a `Site` relationship
and automatically filter objects based on the currently
active site via `augmentSQL`.
