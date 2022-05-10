<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Descriptor;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProcessingDescriptor extends AbstractDescriptor
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     * @Assert\NotBlank
     *
     * @var string
     */
    protected $name = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $author = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $designatedController = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $controllers = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $description = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $lawfulness = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $minimization = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $rightsGuarantee = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $exactness = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $consent = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $concernedPeople = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $contextOfImplementation = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $recipients = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $processors = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $nonEuTransfer = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $lifeCycle = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $storage = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $standards = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $status = '';

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Export"})
     *
     * @var \DateTime|null
     */
    protected $createdAt = '';

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Export"})
     *
     * @var \DateTime|null
     */
    protected $updatedAt = '';

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     */
    protected $pias = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     */
    protected $comments = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     */
    protected $processingDataTypes = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     */
    protected $trackings = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $informedConcernedPeople = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $consentConcernedPeople = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $accessConcernedPeople = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $deleteConcernedPeople = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $limitConcernedPeople = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $subcontractorsObligations = [];

    public function __construct(
        string $name,
        string $author,
        string $designatedController,
        string $controllers = null,
        string $description = null,
        string $lawfulness = null,
        string $minimization = null,
        string $rightsGuarantee = null,
        string $exactness = null,
        string $consent = null,
        string $concernedPeople = null,
        string $contextOfImplementation = null,
        string $recipients = null,
        string $processors = null,
        string $nonEuTransfer = null,
        string $lifeCycle = null,
        string $storage = null,
        string $standards = null,
        string $status = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        array $informedConcernedPeople = null,
        array $consentConcernedPeople = null,
        array $accessConcernedPeople = null,
        array $deleteConcernedPeople = null,
        array $limitConcernedPeople = null,
        array $subcontractorsObligations = null
    ) {
        $this->name = $name;
        $this->author = $author;
        $this->designatedController = $designatedController;
        $this->controllers = $controllers;
        $this->description = $description;
        $this->lawfulness = $lawfulness;
        $this->minimization = $minimization;
        $this->rightsGuarantee = $rightsGuarantee;
        $this->exactness = $exactness;
        $this->consent = $consent;
        $this->concernedPeople = $concernedPeople;
        $this->contextOfImplementation = $contextOfImplementation;
        $this->recipients = $recipients;
        $this->processors = $processors;
        $this->nonEuTransfer = $nonEuTransfer;
        $this->lifeCycle = $lifeCycle;
        $this->storage = $storage;
        $this->standards = $standards;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->informedConcernedPeople = $informedConcernedPeople;
        $this->consentConcernedPeople = $consentConcernedPeople;
        $this->accessConcernedPeople = $accessConcernedPeople;
        $this->deleteConcernedPeople = $deleteConcernedPeople;
        $this->limitConcernedPeople = $limitConcernedPeople;
        $this->subcontractorsObligations = $subcontractorsObligations;
    }

    public function mergePias(array $pias)
    {
        $this->pias = array_merge($this->pias, $pias);
    }

    public function mergeComments(array $comments)
    {
        $this->comments = array_merge($this->comments, $comments);
    }

    public function mergeDataTypes(array $types)
    {
        $this->processingDataTypes = array_merge($this->processingDataTypes, $types);
    }

    public function mergeTrackings(array $trackings)
    {
        $this->trackings = array_merge($this->trackings, $trackings);
    }
}
