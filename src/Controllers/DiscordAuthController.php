<?php

/*
 * This file is part of fof/auth-discord.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\AuthDiscord\Controllers;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;
use Zend\Diactoros\Response\RedirectResponse;

class DiscordAuthController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param ResponseFactory             $response
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings)
    {
        $this->response = $response;
        $this->settings = $settings;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws Exception
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $redirectUri = (string) $request->getAttribute('originalUri', $request->getUri())->withQuery('');

        $provider = new Discord([
            'clientId'     => $this->settings->get('fof-auth-discord.client_id'),
            'clientSecret' => $this->settings->get('fof-auth-discord.client_secret'),
            'redirectUri'  => $redirectUri,
        ]);

        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();

        $code = array_get($queryParams, 'code');

        if (!$code) {
            $authUrl = $provider->getAuthorizationUrl(['scope' => ['identify', 'email']]);
            $session->put('oauth2state', $provider->getState());

            return new RedirectResponse($authUrl.'&display=popup');
        }

        $state = array_get($queryParams, 'state');

        if (!$state || $state !== $session->get('oauth2state')) {
            $session->remove('oauth2state');

            throw new Exception('Invalid state');
        }

        $token = $provider->getAccessToken('authorization_code', compact('code'));

        /** @var DiscordResourceOwner $user */
        $user = $provider->getResourceOwner($token);

        return $this->response->make(
            'discord', $user->getId(),
            function (Registration $registration) use ($user, $provider, $token) {
                $registration
                    ->provideTrustedEmail($user->getEmail())
                    ->provideAvatar($this->getAvatar($user))
                    ->suggestUsername($user->getUsername())
                    ->setPayload($user->toArray());
            }
        );
    }

    private function getAvatar(DiscordResourceOwner $user)
    {
        $hash = $user->getAvatarHash();

        return isset($hash) ? "https://cdn.discordapp.com/avatars/{$user->getId()}/{$user->getAvatarHash()}.png" : null;
    }
}
