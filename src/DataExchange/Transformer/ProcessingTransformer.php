<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\SerializerInterface;
use PiaApi\DataExchange\Descriptor\ProcessingDescriptor;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\ProcessingDataType;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Services\ProcessingService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessingTransformer extends AbstractTransformer
{
    /**
     * @var ProcessingService
     */
    protected $processingService;

    /**
     * @var Folder|null
     */
    protected $folder = null;

    /**
     * @var PiaTransformer
     */
    protected $piaTransformer;

    /**
     * @var ProcessingCommentTransformer
     */
    protected $processingCommentTransformer;

    /**
     * @var DataTypeTransformer
     */
    protected $dataTypeTransformer;

    /**
     * @var TrackingTransformer
     */
    protected $trackingTransformer;

    protected $redactors;
    protected $dataController;
    protected $evaluatorPending;
    protected $dataProtectionOfficerPending;

    public function __construct(
        SerializerInterface $serializer,
        ProcessingService $processingService,
        ValidatorInterface $validator,
        PiaTransformer $piaTransformer,
        ProcessingCommentTransformer $processingCommentTransformer,
        DataTypeTransformer $dataTypeTransformer,
        TrackingTransformer $trackingTransformer
    ) {
        parent::__construct($serializer, $validator);

        $this->redactors = new ArrayCollection();
        $this->processingService = $processingService;
        $this->piaTransformer = $piaTransformer;
        $this->processingCommentTransformer = $processingCommentTransformer;
        $this->dataTypeTransformer = $dataTypeTransformer;
        $this->trackingTransformer = $trackingTransformer;
    }

    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }

    public function addRedactor(User $redactor)
    {
        $this->redactors->add($redactor);
    }

    public function getRedactors(): array
    {
        $redactors = [];
        foreach ($this->redactors as $redactor) {
            array_push($redactors, $redactor);
        }
        return $redactors;
    }

    public function setDataController(User $dataController)
    {
        $this->dataController = $dataController;
    }

    public function getDataController(): User
    {
        return $this->dataController;
    }

    public function setEvaluatorPending(?User $evaluatorPending=null)
    {
        $this->evaluatorPending = $evaluatorPending;
    }

    public function getEvaluatorPending(): ?User
    {
        return $this->evaluatorPending;
    }

    public function setDataProtectionOfficerPending(?User $dataProtectionOfficerPending=null)
    {
        $this->dataProtectionOfficerPending = $dataProtectionOfficerPending;
    }

    public function getDataProtectionOfficerPending(): ?User
    {
        return $this->dataProtectionOfficerPending;
    }

    public function toProcessing(ProcessingDescriptor $descriptor): Processing
    {
        $processing = $this->processingService->createProcessing(
            $descriptor->getName(),
            $this->getFolder(),
            $this->getRedactors(),
            $this->getDataController(),
            $this->getEvaluatorPending(),
            $this->getDataProtectionOfficerPending(),
        );

        $processing->setDescription($descriptor->getDescription());
        $processing->setProcessors($descriptor->getProcessors());
        $processing->setNonEuTransfer($descriptor->getNonEuTransfer());
        $processing->setLifeCycle($descriptor->getLifeCycle());
        $processing->setStorage($descriptor->getStorage());
        $processing->setControllers($descriptor->getControllers());
        $processing->setStandards($descriptor->getStandards());
        $processing->setStatus((int) $descriptor->getStatus());
        $processing->setLawfulness($descriptor->getLawfulness());
        $processing->setMinimization($descriptor->getMinimization());
        $processing->setRightsGuarantee($descriptor->getRightsGuarantee());
        $processing->setExactness($descriptor->getExactness());
        $processing->setConsent($descriptor->getConsent());
        $processing->setConcernedPeople($descriptor->getConcernedPeople());
        $processing->setRecipients($descriptor->getRecipients());
        $processing->setContextOfImplementation($descriptor->getContextOfImplementation());
        $processing->setInformedConcernedPeople($descriptor->getInformedConcernedPeople());
        $processing->setConsentConcernedPeople($descriptor->getConsentConcernedPeople());
        $processing->setAccessConcernedPeople($descriptor->getAccessConcernedPeople());
        $processing->setDeleteConcernedPeople($descriptor->getDeleteConcernedPeople());
        $processing->setLimitConcernedPeople($descriptor->getLimitConcernedPeople());
        $processing->setSubcontractorsObligations($descriptor->getSubcontractorsObligations());

        // Datatypes and other subobjects data
        $this->dataTypeTransformer->setProcessing($processing);
        foreach ( $descriptor->getProcessingDataTypes() as $datatype ) {
          $processing->addProcessingDataType($this->dataTypeTransformer->jsonToDataType($datatype));
        }
        // other subobjects are skipped because, such as PIAs or comments, they have not to be duplicated when duplicating a Processing

        return $processing;
    }

    public function fromProcessing(Processing $processing): ProcessingDescriptor
    {
        $descriptor = new ProcessingDescriptor(
            $processing->getName(),
            $processing->getAuthor(),
            $processing->getDesignatedController(),
            $processing->getControllers(),
            $processing->getDescription(),
            $processing->getLawfulness(),
            $processing->getMinimization(),
            $processing->getRightsGuarantee(),
            $processing->getExactness(),
            $processing->getConsent(),
            $processing->getConcernedPeople(),
            $processing->getContextOfImplementation(),
            $processing->getRecipients(),
            $processing->getProcessors(),
            $processing->getNonEuTransfer(),
            $processing->getLifeCycle(),
            $processing->getStorage(),
            $processing->getStandards(),
            $processing->getStatusName(),
            $processing->getCreatedAt(),
            $processing->getUpdatedAt(),
            $processing->getInformedConcernedPeople(),
            $processing->getConsentConcernedPeople(),
            $processing->getAccessConcernedPeople(),
            $processing->getDeleteConcernedPeople(),
            $processing->getLimitConcernedPeople(),
            $processing->getSubcontractorsObligations()
        );

        $descriptor->mergePias(
            $this->piaTransformer->importPias($processing->getPias())
        );

        $descriptor->mergeComments(
            $this->processingCommentTransformer->importComments($processing->getComments())
        );

        $descriptor->mergeDataTypes(
            $this->dataTypeTransformer->importDataTypes($processing->getProcessingDataTypes())
        );

        $descriptor->mergeTrackings(
            $this->trackingTransformer->importTrackings($processing->getTrackingsObjectList())
        );

        return $descriptor;
    }

    public function processingToJson(Processing $processing): string
    {
        $descriptor = $this->fromProcessing($processing);
        return $this->toJson($descriptor);
    }

    public function jsonToProcessing(array $json): Processing
    {
        $descriptor = $this->fromJson($json, ProcessingDescriptor::class);

        return $this->toProcessing($descriptor);
    }

    public function extractPia(Processing $processing, array $json): Pia
    {
        $this->piaTransformer->setProcessing($processing);

        return $this->piaTransformer->jsonToPia($json);
    }

    public function extractDataType(Processing $processing, array $json): ProcessingDataType
    {
        $this->dataTypeTransformer->setProcessing($processing);

        return $this->dataTypeTransformer->jsonToDataType($json);
    }
}
