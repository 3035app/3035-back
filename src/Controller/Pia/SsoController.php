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
     * @FOSRest\Get("/authBySso/{code}")
     */
    public function __invoke(string $code)
    {
        new Client([
            'base_uri' => 'URL pour récupérer les infos sur la plateforme SSO',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        return new JsonResponse('TODO');
    }
}
