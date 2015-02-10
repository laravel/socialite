<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class YoutubeProvider extends GoogleProvider implements ProviderInterface {

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = [
        'https://www.googleapis.com/auth/youtube',
	];

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/youtube/v3/channels',
            [
                'query' => [
                    'part' => 'id,snippet,contentDetails,statistics,topicDetails,invideoPromotion',
                    'mine' => 'true',
                    'prettyPrint' => 'false',
                    'key' => $this->apiKey,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
		]);
        
        return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
        $userItem = $user['items'][0];
        $thumbs = array_get($userItem['snippet'], 'thumbnails', []);
        
		return (new User)->setRaw($user)->map([
			'id' => $userItem['id'], 'nickname' => $userItem['snippet']['title'], 'name' => null,
			'email' => null, 'avatar' => array_get($thumbs, 'medium', array_get($thumbs, 'default'))['url'],
		]);
	}

}
