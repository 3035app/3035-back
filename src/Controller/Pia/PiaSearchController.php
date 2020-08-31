<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use JMS\Serializer\SerializerInterface;
use PiaApi\Services\PiaSearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PiaApi\Model\SearchResultModel;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;

class PiaSearchController extends RestController
{
    /**
     * @var PiaSearchService
     */
    private $searchService;

    public function __construct(PropertyAccessorInterface $propertyAccessor, SerializerInterface $serializer, PiaSearchService $searchService)
    {
        parent::__construct($propertyAccessor, $serializer);
        $this->searchService = $searchService;
    }

    /**
     * @Swg\Tag(name="Searxch")
     *
     * @FOSRest\Post("/search")
     *
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Search for structures, processings and folders",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=SearchResultModel::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function searchAction(Request $request)
    {
        $name = $request->get('value');
        $results = $this->searchService->search($name);

        return $this->view($results, Response::HTTP_OK);
    }

    protected function getEntityClass()
    {
        return SearchResultModel::class;
    }
}
