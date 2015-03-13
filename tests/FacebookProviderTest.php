<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookProviderTest extends PHPUnit_Framework_TestCase {

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

		$provider = new FacebookProvider($request, 'client_id', 'client_secret', 'redirect');

		$response = $provider->redirect(true);

		$this->assertEquals('https://www.facebook.com/v2.2/dialog/oauth?client_id=client_id&redirect_uri=redirect&scope=email&state='.$state.'&response_type=code&auth_type=rerequest', $response->getTargetUrl());
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

		$provider = new FacebookProvider($request, 'client_id', 'client_secret', 'redirect');

		$response = $provider->redirect();

		$this->assertEquals('https://www.facebook.com/v2.2/dialog/oauth?client_id=client_id&redirect_uri=redirect&scope=email&state='.$state.'&response_type=code', $response->getTargetUrl());
	}

}
