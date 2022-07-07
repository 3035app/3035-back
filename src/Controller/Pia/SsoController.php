<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use Codeception\Lib\Connector\Guzzle;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Util\Json;
use PiaApi\Entity\Oauth\User;
use PiaApi\Repository\UserRepository;
use PiaApi\Services\PiaSearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PiaApi\Model\SearchResultModel;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use GuzzleHttp\Client;


class SsoController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * @FOSRest\Get("/authBySso/{code}")
     */
    public function __invoke(string $code)
    {
        $client = new Client([
            'base_uri' => 'https://idp-dev.sncf.fr:443/openam/oauth2/IDP/',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->request('POST', 'access_token', [
            'headers' => [
                'Authorization' => 'Basic UGlhbGFiOnp3b1BERUZBdDJEYWxpUjFQSTlW',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => 'http://localhost:4200/callback/'
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
                'redirect_uri' => 'http://localhost:4200/callback/'
            ]
        ]);

        $user = $this->userRepository->findOneBy([
           'usernameForSncfConnect' => json_decode($response->getBody()->getContents(), true)['sub']
        ]);

        if (!$user instanceof User) {
            return new JsonResponse('User not found for this code', 404);
        }



        return new JsonResponse($response->getBody()->getContents(), 200, [], true);
    }
}
