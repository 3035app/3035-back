<?php

namespace PiaApi\Auth;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use OAuth2\OAuth2;
use PiaApi\Entity\Oauth\User;
use PiaApi\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SncfConnect
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OAuth2
     */
    private $server;
    /**
     * @var string
     */
    private $sncfConnectId;
    /**
     * @var string
     */
    private $sncfConnectSecret;
    /**
     * @var string
     */
    private $sncfConnectUrl;

    public function __construct(
        EntityManagerInterface $entityManager,
        OAuth2 $OAuth2,
        string $sncfConnectUrl,
        string $sncfConnectId,
        string $sncfConnectSecret
    )
    {
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->server = $OAuth2;
        $this->sncfConnectUrl = $sncfConnectUrl;
        $this->sncfConnectId = $sncfConnectId;
        $this->sncfConnectSecret = $sncfConnectSecret;
    }

    public function generateToken(string $code, string $redirectUri)
    {
        $client = new Client([
            'base_uri' => $this->sncfConnectUrl,
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->request('POST', 'access_token', [
            'headers' => [
                'Authorization' => 'Basic '.base64_encode(sprintf('%s:%s', $this->sncfConnectId, $this->sncfConnectSecret)),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri
            ]
        ]);

        $accessToken = json_decode($response->getBody()->getContents(), true)['access_token'];

        $response = $client->request('POST', 'userinfo', [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri
            ]
        ]);

        $user = $this->userRepository->findOneBy([
            'usernameForSncfConnect' => json_decode($response->getBody()->getContents(), true)['sub']
        ]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found for this code');
        }

        return $this->server->createAccessToken($user->getApplication(), $user, null, 3600, true, 1209600);
    }
}