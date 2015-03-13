<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleProviderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testRerequestParameterAddedToUrl()
	{
		$request = Request::create('foo');
		$request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
		$session->shouldReceive('get')->once()->with('_token')->andReturn('token');

		$state = null;

		$session->shouldReceive('set')->once()->with('state',m::on(function($s) use (&$state) {
			$state = $s;
			return true;	
		}));

		$provider = new GoogleProvider($request, 'client_id', 'client_secret', 'redirect');

		$response = $provider->redirect(true);

		$this->assertEquals('https://accounts.google.com/o/oauth2/auth?client_id=client_id&redirect_uri=redirect&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.me+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.login+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.profile.emails.read&state='.$state.'&response_type=code&approval_prompt=force', $response->getTargetUrl());
	}

	public function testRerequestParameterMissingByDefault()
	{
		$request = Request::create('foo');
		$request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
		$session->shouldReceive('get')->once()->with('_token')->andReturn('token');

		$state = null;

		$session->shouldReceive('set')->once()->with('state',m::on(function($s) use (&$state) {
			$state = $s;
			return true;	
		}));

		$provider = new GoogleProvider($request, 'client_id', 'client_secret', 'redirect');

		$response = $provider->redirect();

		$this->assertEquals('https://accounts.google.com/o/oauth2/auth?client_id=client_id&redirect_uri=redirect&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.me+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.login+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.profile.emails.read&state='.$state.'&response_type=code', $response->getTargetUrl());
	}

}
