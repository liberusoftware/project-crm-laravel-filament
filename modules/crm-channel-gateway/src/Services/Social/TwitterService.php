<?php

namespace Liberu\CRM\ChannelGateway\Services\Social;

use Abraham\TwitterOAuth\TwitterOAuth;
use Liberu\Foundation\Identity\Socialstream\Models\ConnectedAccount;

class TwitterService
{
    protected TwitterOAuth $connection;

    public function __construct()
    {
        $this->connection = new TwitterOAuth(
            config('services.twitter.consumer_key'),
            config('services.twitter.consumer_secret')
        );
    }

    public function getTimeline(ConnectedAccount $account)
    {
        $this->connection->setOauthToken($account->token, $account->secret);

        return $this->connection->get('statuses/home_timeline', ['count' => 25, 'exclude_replies' => true]);
    }

    public function postTweet(ConnectedAccount $account, $message)
    {
        $this->connection->setOauthToken($account->token, $account->secret);

        return $this->connection->post('statuses/update', ['status' => $message]);
    }

    public function getAllConnectedAccounts()
    {
        return ConnectedAccount::ofType('twitter')->get();
    }

    public function getPrimaryAccount()
    {
        return ConnectedAccount::ofType('twitter')->primary()->first();
    }
}
