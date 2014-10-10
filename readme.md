# Laravel Socialite

## Getting Started (BETA - Laravel 5.0+)

### Add Configuration

First, you should configure the authentication providers you would like to use in your `config/services.php` file.

	'twitter' => [
		'client_id' => 'your-client-id',
		'client_secret' => 'your-client-secret',
		'redirect' => 'http://yourapp.com/auth/twitter/callback'
	],

### Redirect To The OAuth Provider

```php
	<?php

	use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

	class YourController extends BaseController {

		public function __construct(SocialiteFactory $socialite)
		{
			$this->socialite = $socialite;
		}

		public function redirectToTwitter()
		{
			return $this->socialite->driver('twitter')->redirect();
		}

	}
```

You may also add scopes to the authentication call:

```php
	public function redirectToTwitter()
	{
		return $this->socialite->driver('twitter')
                            ->scopes(['scope1', 'scope2'])
                            ->redirect();
	}
```

### Capture The User Details

After the user accepts the authentication prompt on the provider:

```php
	<?php

	use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

	class YourController extends BaseController {

		public function __construct(SocialiteFactory $socialite)
		{
			$this->socialite = $socialite;
		}

		public function getUserDetails()
		{
			$user = $this->socialite->driver('twitter')->user();
		}

	}
```

The `user` method returns an implementation of `Laravel\Socialite\Contracts\User`.
