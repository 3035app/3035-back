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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Security;

abstract class LayerRestController extends RestController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer,
        Security $security
    ) {
        parent::__construct($propertyAccessor, $serializer);
        $this->security = $security;
    }

    /**
     * Gets Security resource.
     *
     * @return Security
     */
    protected function getSecurity(): Security
    {
        return $this->security;
    }
}
