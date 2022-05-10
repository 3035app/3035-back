<?php

/*
 * Copyright (C) 2015-2019 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Transformer;

use PiaApi\Entity\Pia\ProcessingComment;
use PiaApi\DataExchange\Descriptor\ProcessingCommentDescriptor;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessingCommentTransformer extends AbstractTransformer
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

    public function toComment(ProcessingCommentDescriptor $descriptor): ProcessingComment
    {}

    public function fromComment(ProcessingComment $comment): ProcessingCommentDescriptor
    {
        $descriptor = new ProcessingCommentDescriptor(
            $comment->getContent(),
            $comment->getField(),
            $comment->getCreatedAt(),
            $comment->getUpdatedAt(),
            $comment->getCommentedBy(),
        );
        return $descriptor;
    }

    public function importComments(array $comments): array
    {
        $descriptors = [];
        foreach ($comments as $comment) {
            $descriptors[] = $this->fromComment($comment);
        }
        return $descriptors;
    }

    public function commentToJson(ProcessingComment $comment): string
    {
        $descriptor = $this->fromComment($comment);
        return $this->toJson($descriptor);
    }

    public function jsonToComment(array $json): ProcessingComment
    {
        $descriptor = $this->fromJson($json, ProcessingCommentDescriptor::class);
        return $this->toComment($descriptor);
    }
}
