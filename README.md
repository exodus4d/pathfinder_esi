### [_EVE-Online_](https://www.eveonline.com) - [_ESI_ API](https://esi.tech.ccp.is) client library for [_Pathfinder_](https://github.com/exodus4d/pathfinder)

This ESI API client handles all _ESI_ API calls within _Pathfinder_ (`>= v.1.2.3`) and implements all required endpoints.

#### Installation 
This _ESI_ client is automatically installed through [_Composer_](https://getcomposer.org/) with all dependencies from your _Pathfinder_ project root. (see [composer.json](https://github.com/exodus4d/pathfinder/blob/master/composer.json)).

A newer version of _Pathfinder_ **may** require a newer version of this client as well. So running `composer install` **after** a _Pathfinder_ update will upgrade/install a newer _ESI_ client.
Check the _Pathfinder_ [release](https://github.com/exodus4d/pathfinder/releases) notes for further information.

#### Bug report
Issues can be tracked here: https://github.com/exodus4d/pathfinder/issues

#### Development
If you are a developer you might have **both** repositories ([exodus4d/pathfinder](https://github.com/exodus4d/pathfinder), [exodus4d/pathfinder_esi](https://github.com/exodus4d/pathfinder_esi) ) checked out locally.

In this case you probably want to _test_ changes in your **local** [exodus4d/pathfinder_esi](https://github.com/exodus4d/pathfinder_esi) repo using your **local** [exodus4d/pathfinder](https://github.com/exodus4d/pathfinder) installation.

1. Clone/Checkout **both** repositories local next to each other
2. Make your changes in your pathfinder_esi repo and **commit** changes (no push!)
3. Switch to your pathfinder repo
4. Run _Composer_ with [`composer-dev.json`](https://github.com/exodus4d/pathfinder/blob/master/composer-dev.json), which installs pathfinder_esi from your **local** repository.
    - Unix: `$set COMPOSER=composer-dev.json && composer update`
    - Windows (PowerShell): `$env:COMPOSER="composer-dev.json"; composer update --no-suggest`

