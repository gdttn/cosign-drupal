# cosign-drupal
a module to utilize cosign for drupal logins, updated for drupal 9 and 10.

## status

Tested on Drupal 9.5 and 10.0.

This has been tested on one (1) local installation.  It should be usable
anywhere Cosign is required with Drupal 9+ however reviewing the code is highly
recommended before use; there could be site-specific code or other bugs lurking.

Bugfix PRs are welcomed.  I don't need new features, as far as I'm aware this 
is a dead-end technology and maintained for legacy purposes.


## installation
   * Check out / unzip into `web/modules/contrib/cosign`
      * example text to add to composer.json:
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/gdttn/cosign-drupal"
        }
    ],
}
```
   * Then, `composer require gdttn/cosign [...]`
   * Enable the module: `drush pm:enable cosign` 
   * Configure module (in "system") or `drush config:edit cosign.settings` to point to your Cosign web service

Remember, it is recommended that you familiarise yourself with `drush`
configuration since a misconfiguration via Drupal's admin interface is highly
likely to leave you locked out.


## Originally documented .htaccess changes (if required)

   * Add `RewriteRule ^cosign/valid - [L]` immediately below `RewriteEngine on` to prevent Drupal handling cosign requests.
   * At a bare minimum, ensure the _Location_ `/user/login` has
     `CosignProtected On`; the remainder can be unprotected if you wish to
     allow anonymous browsing.

   * _If you wish to enforce use of SSL_ on Apache servers you could include the old Drupal 7 boilerplate:
```
  RewriteRule ^ - [E=protossl]
  RewriteCond %{HTTPS} on
  RewriteRule ^ - [E=protossl:s]
```
   but this hasn't been tested on this version, and relying on the module to do
   this appears broken - enforcing HTTPS at all times via the web server is now
   the only recommended way to use this plugin.


## TODO:
   * make the tests work; expand them
   * document the settings properly
   * improve debug / watchdog logs
   * work out redirect issue
   * Produce sample config / use a variant of the protossl environment variable to partition auth users on one vhost, anons on another.
