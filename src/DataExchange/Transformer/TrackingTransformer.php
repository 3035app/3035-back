<?php

/*
 * Copyright (C) 2015-2019 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Transformer;

use PiaApi\Entity\Pia\TrackingLog;
use PiaApi\DataExchange\Descriptor\TrackingDescriptor;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackingTransformer extends AbstractTransformer
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var PiaService
     */
    protected $piaService;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function toTracking(TrackingDescriptor $descriptor): TrackingLog
    {}

    public function fromTracking(TrackingLog $tracking): TrackingDescriptor
    {
        $descriptor = new TrackingDescriptor(
            $tracking->getActivity(),
            $tracking->getOwner()->getProfile()->getFullname(),
            $tracking->getDate()
        );
        return $descriptor;
    }

    public function importTrackings(array $trackings): array
    {
        $descriptors = [];
        foreach ($trackings as $tracking) {
            $descriptors[] = $this->fromTracking($tracking);
        }
        return $descriptors;
    }

    public function trackingToJson(TrackingLog $tracking): string
    {
        $descriptor = $this->fromTracking($tracking);
        return $this->toJson($descriptor);
    }

    public function jsonToTracking(array $json): TrackingLog
    {
        $descriptor = $this->fromJson($json, TrackingDescriptor::class);
        return $this->toTracking($descriptor);
    }
}
