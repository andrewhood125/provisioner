provisioner
===========

Deploy your laravel projects straight from `artisan`.

### Getting Started

Add provisioner to your laravel project.
`composer require andrewhood125/provisioner --dev`

Add the hook for your local environment only in `app/Providers/AppServiceProvider.php`

```
public function register()
{
    if($this->app->environment() == 'local') {
        $this->app->register('Andrewhood125\Provisioner\ProvisionerServiceProvider');
    }
}
```

### Provisioning

Before you provision you need to have a server spun up with a root user.

```
./artisan remote:provision:debian example.org
```

Once the server is provisioned you can install a site.

```
./artisan remote:install andrewhood125/ondamanda example.org
```

Once a site is installed you can deploy. At this point you still need to copy your environment variables over do any migrations and a composer install.


### Notes

Having trouble cloning a private repository? (Troubleshooting SSH agent forwarding)[https://developer.github.com/guides/using-ssh-agent-forwarding/#troubleshooting-ssh-agent-forwarding].
