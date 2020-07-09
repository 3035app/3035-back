<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Form\Portfolio\Transformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use PiaApi\Entity\Pia\Portfolio;

class PortfolioTransformer implements DataTransformerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function transform($value)
    {
        if ($value instanceof Portfolio) {
            return $value->getId();
        }

        return null;
    }

    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }

        return $this->doctrine->getManager()->getRepository(Portfolio::class)->find($value);
    }
}
