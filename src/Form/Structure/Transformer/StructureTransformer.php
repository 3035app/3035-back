<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Form\Structure\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use PiaApi\Entity\Pia\Structure;

class StructureTransformer implements DataTransformerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function transform($value)
    {
        if ($value instanceof Structure) {
            return $value->getId();
        }

        return -1;
    }

    public function reverseTransform($value)
    {
        return $this->doctrine->getManager('default')->getRepository(Structure::class)->find($value);
    }
}
