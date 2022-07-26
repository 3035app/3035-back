<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use PiaApi\Auth\SncfConnect;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as FOSRest;


class SsoController
{
    private $sncfConnect;

    public function __construct(SncfConnect $sncfConnect)
    {
        $this->sncfConnect = $sncfConnect;
    }
    /**
     * @FOSRest\Get("/authBySso/{code}")
     */
    public function __invoke(Request $request, string $code)
    {

        $token = $this->sncfConnect->generateToken($code, $request->query->get('redirect_uri'));

        return new Response(json_encode($token), 200);
    }
}
