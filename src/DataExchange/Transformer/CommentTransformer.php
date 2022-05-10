<?php

/*
 * Copyright (C) 2015-2019 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Transformer;

use PiaApi\Entity\Pia\Comment;
use PiaApi\DataExchange\Descriptor\CommentDescriptor;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentTransformer extends AbstractTransformer
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

    public function toComment(CommentDescriptor $descriptor): Comment
    {}

    public function fromComment(Comment $comment): CommentDescriptor
    {
        $descriptor = new CommentDescriptor(
            $comment->getDescription(),
            $comment->getReferenceTo(),
            $comment->getForMeasure(),
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

    public function commentToJson(Comment $comment): string
    {
        $descriptor = $this->fromComment($comment);
        return $this->toJson($descriptor);
    }

    public function jsonToComment(array $json): Comment
    {
        $descriptor = $this->fromJson($json, CommentDescriptor::class);
        return $this->toComment($descriptor);
    }
}
