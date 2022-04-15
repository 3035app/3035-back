<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Traits\ProcessingSupervisorTrait;
use PiaApi\Entity\Pia\Traits\ResourceTrait;
use PiaApi\Entity\Pia\Traits\TrackingTrait;

/**
 * @ORM\Entity(repositoryClass="PiaApi\Repository\ProcessingRepository")
 * @ORM\Table(name="pia_processing")
 */
class Processing implements ObjectManagerAware, TrackingInterface
{
    use ProcessingSupervisorTrait, ResourceTrait, TrackingTrait;

    const STATUS_DOING = 0;
    const STATUS_UNDER_VALIDATION = 1;
    const STATUS_VALIDATED = 2;
    const STATUS_ARCHIVED = 3;

    const EVALUATION_STATE_NONE = -1;
    const EVALUATION_STATE_TO_CORRECT = 0;
    const EVALUATION_STATE_IMPROVABLE = 1;
    const EVALUATION_STATE_ACCEPTABLE = 2;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $author;

    /**
     * @ORM\Column(type="integer", options={"default": Processing::STATUS_DOING})
     * @JMS\Groups({"Default", "Export"})
     *
     * @var int
     */
    protected $status = ProcessingStatus::STATUS_DOING;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $lifeCycle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $storage;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $standards;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $processors;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $designatedController;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $controllers;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $nonEuTransfer;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $recipients;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $contextOfImplementation;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $lawfulness;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $minimization;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $rightsGuarantee;
    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $exactness;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $consent;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $concernedPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string|null
     */
    protected $evaluationComment;

    /**
     * @ORM\Column(type="integer", options={"default": Processing::EVALUATION_STATE_NONE})
     * @JMS\Groups({"Default", "Export"})
     *
     * @var int
     */
    protected $evaluationState = Processing::EVALUATION_STATE_NONE;

    /**
     * @ORM\OneToMany(targetEntity="ProcessingDataType", mappedBy="processing", cascade={"persist", "remove"})
     * @JMS\Groups({"Default", "Export"})
     * @JMS\MaxDepth(2)
     *
     * @var Collection|ProcessingDataType[]
     */
    protected $processingDataTypes;

    /**
     * @ORM\OneToMany(targetEntity="ProcessingComment", mappedBy="processing", cascade={"remove"})
     * @JMS\Groups({"Default", "Export"})
     * @JMS\MaxDepth(2)
     *
     * @var Collection|ProcessingComment[]
     */
    protected $comments;
    
     /**
     * @ORM\OneToMany(targetEntity="ProcessingAttachment", mappedBy="processing", cascade={"remove"})
     * @JMS\Groups({"Default", "Export"})
     * @JMS\MaxDepth(2)
     *
     * @var Collection|ProcessingAttachment[]
     */
    protected $attachments;

    /**
     * @ORM\OneToMany(targetEntity="Pia", mappedBy="processing", cascade={"persist"})
     * @JMS\Groups({"Default", "Export"})
     * @JMS\Exclude()
     *
     * @var Collection|Pia[]
     */
    protected $pias;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="processings")
     * @JMS\Groups({"Default"})
     * @JMS\MaxDepth(1)
     *
     * @var Folder
     */
    protected $folder;

    /**
     * @ORM\ManyToOne(targetEntity="ProcessingTemplate", inversedBy="processings")
     * @JMS\Groups({"Full"})
     *
     * @var ProcessingTemplate
     */
    protected $template;

    /**
     * many processings have many users.
     * @ORM\ManyToMany(targetEntity="PiaApi\Entity\Oauth\User")
     * @ORM\JoinTable(name="pia_users__processings")
     * @JMS\Exclude()
     * 
     * @var Collection
     */
    protected $users;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * @var bool
     */
    protected $canShow;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $informedConcernedPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $consentConcernedPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $accessConcernedPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $deleteConcernedPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $limitConcernedPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array|null
     */
    protected $subcontractorsObligations;

    public function __construct(
        string $name,
        Folder $folder,
        User $redactor,
        User $dataController,
        User $evaluatorPending=null,
        User $dataProtectionOfficerPending=null
    ) {
        $this->setName($name);
        $this->setFolder($folder);
        $this->setRedactor($redactor);
        $this->setDataController($dataController);
        $this->setEvaluatorPending($evaluatorPending);
        $this->setDataProtectionOfficerPending($dataProtectionOfficerPending);

        $this->processingDataTypes = new ArrayCollection();
        $this->pias = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getLifeCycle(): ?string
    {
        return $this->lifeCycle;
    }

    /**
     * @param string $lifeCycle
     */
    public function setLifeCycle(?string $lifeCycle = null): void
    {
        $this->lifeCycle = $lifeCycle;
    }

    /**
     * @return string|null
     */
    public function getDataMedium(): ?string
    {
        return $this->dataMedium;
    }

    /**
     * @param string $dataMedium
     */
    public function setDataMedium(?string $dataMedium = null): void
    {
        $this->dataMedium = $dataMedium;
    }

    /**
     * @return string
     */
    public function getStandards(): ?string
    {
        return $this->standards;
    }

    /**
     * @param string $standards
     */
    public function setStandards(?string $standards): void
    {
        $this->standards = $standards;
    }

    /**
     * @return string|null
     */
    public function getProcessors(): ?string
    {
        return $this->processors;
    }

    /**
     * @param string $processors
     */
    public function setProcessors(?string $processors = null): void
    {
        $this->processors = $processors;
    }

    /**
     * @return string
     */
    public function getControllers(): ?string
    {
        return $this->controllers;
    }

    /**
     * @param string $controllers
     */
    public function setControllers(?string $controllers = null): void
    {
        $this->controllers = $controllers;
    }

    /**
     * @return string
     */
    public function getLawfulness(): ?string
    {
        return $this->lawfulness;
    }

    /**
     * @param string $lawfulness
     */
    public function setLawfulness(?string $lawfulness = null): void
    {
        $this->lawfulness = $lawfulness;
    }

    /**
     * @return string
     */
    public function getMinimization(): ?string
    {
        return $this->minimization;
    }

    /**
     * @param string $minimization
     */
    public function setMinimization(?string $minimization = null): void
    {
        $this->minimization = $minimization;
    }

    /**
     * @return string
     */
    public function getRightsGuarantee(): ?string
    {
        return $this->rightsGuarantee;
    }

    /**
     * @param string $rightsGuarantee
     */
    public function setRightsGuarantee(?string $rightsGuarantee = null): void
    {
        $this->rightsGuarantee = $rightsGuarantee;
    }

    /**
     * @return string
     */
    public function getExactness(): ?string
    {
        return $this->exactness;
    }

    /**
     * @param string $exactness
     */
    public function setExactness(?string $exactness = null): void
    {
        $this->exactness = $exactness;
    }

    /**
     * @return string
     */
    public function getConsent(): ?string
    {
        return $this->consent;
    }

    /**
     * @param string $consent
     */
    public function setConsent(?string $consent = null): void
    {
        $this->consent = $consent;
    }

    /**
     * @return string
     */
    public function getConcernedPeople(): ?string
    {
        return $this->concernedPeople;
    }

    /**
     * @param string $concernedPeople
     */
    public function setConcernedPeople(?string $concernedPeople = null): void
    {
        $this->concernedPeople = $concernedPeople;
    }

    /**
     * @return array|ProcessingComment[]
     */
    public function getComments(): array
    {
        return $this->comments->getValues();
    }

    /**
     * @param ProcessingComment $comment
     *
     * @throws \InvalidArgumentException
     */
    public function addComment(ProcessingComment $comment): void
    {
        if ($this->comments->contains($comment)) {
            throw new \InvalidArgumentException(sprintf('Comment « %s » already belongs to Processing « #%d »', $comment->getId(), $this->getId()));
        }
        $this->comments->add($comment);
    }

    /**
     * @param ProcessingComment $comment
     *
     * @throws \InvalidArgumentException
     */
    public function removeComment(ProcessingComment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            throw new \InvalidArgumentException(sprintf('Comment « %s » does not belong to Processing « #%d »', $comment->getId(), $this->getId()));
        }
        $this->comments->removeElement($comment);
    }

    /**
     * @return array|ProcessingAttachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments->getValues();
    }

    /**
     * @param ProcessingAttachment $attachment
     *
     * @throws \InvalidArgumentException
     */
    public function addAttachment(ProcessingAttachment $attachment): void
    {
        if ($this->attachments->contains($attachment)) {
            throw new \InvalidArgumentException(sprintf('Attachment « %s » already belongs to Processing « #%d »', $attachment->getId(), $this->getId()));
        }
        $this->attachments->add($attachment);
    }

    /**
     * @param ProcessingAttachment $attachment
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttachment(ProcessingAttachment $attachment): void
    {
        if (!$this->attachments->contains($attachment)) {
            throw new \InvalidArgumentException(sprintf('Attachment « %s » does not belong to Processing « #%d »', $attachment->getId(), $this->getId()));
        }
        $this->attachments->removeElement($attachment);
    }

    /**
     * @return array|ProcessingDataType[]
     */
    public function getProcessingDataTypes(): array
    {
        return $this->processingDataTypes->getValues();
    }

    /**
     * @param ProcessingDataType $processingDataType
     *
     * @throws \InvalidArgumentException
     */
    public function addProcessingDataType(ProcessingDataType $processingDataType): void
    {
        if ($this->processingDataTypes->contains($processingDataType)) {
            throw new \InvalidArgumentException(sprintf('ProcessingDataType « %s » already belongs to Processing « #%d »', $processingDataType->getId(), $this->getId()));
        }
        $this->processingDataTypes->add($processingDataType);
    }

    /**
     * @param ProcessingDataType $processingDataType
     *
     * @throws \InvalidArgumentException
     */
    public function removeProcessingDataType(ProcessingDataType $processingDataType): void
    {
        if (!$this->processingDataTypes->contains($processingDataType)) {
            throw new \InvalidArgumentException(sprintf('ProcessingDataType « %s » does not belong to Processing « #%d »', $processingDataType->getId(), $this->getId()));
        }
        $this->processingDataTypes->removeElement($processingDataType);
    }

    /**
     * @JMS\VirtualProperty("piasCount")
     * @JMS\Groups({"Default", "Export"})
     *
     * @return int
     */
    public function getPiasCount(): int
    {
        if (null == $this->pias) {
            return 0;
        }
        return $this->pias->count();
    }

    /**
     * @return array|Pia[]
     */
    public function getPias(): array
    {
        return $this->pias->getValues();
    }

    /**
     * @param Pia $pia
     *
     * @throws \InvalidArgumentException
     */
    public function addPia(Pia $pia): void
    {
        if ($this->pias->contains($pia)) {
            throw new \InvalidArgumentException(sprintf('Pia « %s » is already linked to Processing « #%d »', $pia->getId(), $this->getId()));
        }
        $this->pias->add($pia);
    }

    /**
     * @param Pia $pia
     *
     * @throws \InvalidArgumentException
     */
    public function removePia(Pia $pia): void
    {
        if (!$this->pias->contains($pia)) {
            throw new \InvalidArgumentException(sprintf('Pia « %s » is not linked to Processing « #%s »', $pia->getId(), $this->getId()));
        }
        $this->pias->removeElement($pia);
    }

    /**
     * @return Folder
     */
    public function getFolder(): Folder
    {
        return $this->folder;
    }

    /**
     * @param Folder $folder
     */
    public function setFolder(Folder $folder): void
    {
        $this->folder = $folder;
    }

    /**
     * @return string
     */
    public function getNonEuTransfer(): ?string
    {
        return $this->nonEuTransfer;
    }

    /**
     * @param string $nonEuTransfer
     */
    public function setNonEuTransfer(?string $nonEuTransfer): void
    {
        $this->nonEuTransfer = $nonEuTransfer;
    }

    /**
     * @return string
     */
    public function getStorage(): ?string
    {
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage(?string $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusName(): string
    {
        return ProcessingStatus::getStatusName($this->status);
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        if (!in_array($status, [
            self::STATUS_DOING,
            self::STATUS_UNDER_VALIDATION,
            self::STATUS_VALIDATED,
            self::STATUS_ARCHIVED,
        ])) {
            throw new \InvalidArgumentException(sprintf('Status « %d » is not valid', $status));
        }
        $this->status = $status;
    }

    /**
     * @return ProcessingTemplate
     */
    public function getTemplate(): ?ProcessingTemplate
    {
        return $this->template;
    }

    /**
     * @param ProcessingTemplate $template
     */
    public function setTemplate(?ProcessingTemplate $template): void
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getDesignatedController(): string
    {
        return $this->designatedController;
    }

    /**
     * @param string $designatedController
     */
    public function setDesignatedController(string $designatedController): void
    {
        $this->designatedController = $designatedController;
    }

    /**
     * @return string|null
     */
    public function getRecipients(): ?string
    {
        return $this->recipients;
    }

    /**
     * @param string|null $recipients
     */
    public function setRecipients(?string $recipients = null): void
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string|null
     */
    public function getContextOfImplementation(): ?string
    {
        return $this->contextOfImplementation;
    }

    /**
     * @param string|null $contextOfImplementation
     */
    public function setContextOfImplementation(?string $contextOfImplementation = null): void
    {
        $this->contextOfImplementation = $contextOfImplementation;
    }

    /**
     * @return int
     */
    public function getEvaluationState(): int
    {
        return $this->evaluationState;
    }

    /**
     * @param int $evaluationState
     */
    public function setEvaluationState(int $evaluationState): void
    {
        $availableStates = [
            Processing::EVALUATION_STATE_NONE,
            Processing::EVALUATION_STATE_TO_CORRECT,
            Processing::EVALUATION_STATE_IMPROVABLE,
            Processing::EVALUATION_STATE_ACCEPTABLE,
        ];
        if (!in_array($evaluationState, $availableStates)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Evaluation state « %d » is not valid. Valid states are « %s »',
                    $evaluationState,
                    implode(', ', $availableStates)
                )
            );
        }

        if ($this->evaluationState === $evaluationState) {
            // Skipping status management if evaluation state is not changed
            return;
        }

        if (
            $evaluationState === Processing::EVALUATION_STATE_ACCEPTABLE ||
            $evaluationState === Processing::EVALUATION_STATE_IMPROVABLE
        ) {
            // Self validate if Processing is evaluated as acceptable
            $this->setStatus(Processing::STATUS_VALIDATED);
        } elseif (
            $evaluationState === Processing::EVALUATION_STATE_TO_CORRECT
        ) {
            // Go back to draft (Doing) if Processing is evaluated as not correct
            $this->setStatus(Processing::STATUS_DOING);
        }

        $this->evaluationState = $evaluationState;
    }

    /**
     * @return string|null
     */
    public function getEvaluationComment(): ?string
    {
        return $this->evaluationComment;
    }

    /**
     * @param string|null $evaluationComment
     */
    public function setEvaluationComment(?string $evaluationComment = null): void
    {
        $this->evaluationComment = $evaluationComment;
    }

    /**
     * @return array|null
     */
    public function getInformedConcernedPeople(): ?array
    {
        return $this->informedConcernedPeople;
    }

    /**
     * @param array|null $informedConcernedPeople
     */
    public function setInformedConcernedPeople(?array $informedConcernedPeople = null): void
    {
        $this->informedConcernedPeople = $informedConcernedPeople;
    }

    /**
     * @return array|null
     */
    public function getConsentConcernedPeople(): ?array
    {
        return $this->consentConcernedPeople;
    }

    /**
     * @param array|null $consentConcernedPeople
     */
    public function setConsentConcernedPeople(?array $consentConcernedPeople = null): void
    {
        $this->consentConcernedPeople = $consentConcernedPeople;
    }

    /**
     * @return array|null
     */
    public function getAccessConcernedPeople(): ?array
    {
        return $this->accessConcernedPeople;
    }

    /**
     * @param array|null $accessConcernedPeople
     */
    public function setAccessConcernedPeople(?array $accessConcernedPeople = null): void
    {
        $this->accessConcernedPeople = $accessConcernedPeople;
    }

    /**
     * @return array|null
     */
    public function getDeleteConcernedPeople(): ?array
    {
        return $this->deleteConcernedPeople;
    }

    /**
     * @param array|null $deleteConcernedPeople
     */
    public function setDeleteConcernedPeople(?array $deleteConcernedPeople = null): void
    {
        $this->deleteConcernedPeople = $deleteConcernedPeople;
    }

    /**
     * @return array|null
     */
    public function getLimitConcernedPeople(): ?array
    {
        return $this->limitConcernedPeople;
    }

    /**
     * @param array|null $limitConcernedPeople
     */
    public function setLimitConcernedPeople(?array $limitConcernedPeople = null): void
    {
        $this->limitConcernedPeople = $limitConcernedPeople;
    }

    /**
     * @return array|null
     */
    public function getSubcontractorsObligations(): ?array
    {
        return $this->subcontractorsObligations;
    }

    /**
     * @param array|null $subcontractorsObligations
     */
    public function setSubcontractorsObligations(?array $subcontractorsObligations = null): void
    {
        $this->subcontractorsObligations = $subcontractorsObligations;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @return bool
     */
    public function canShow(User $user): bool
    {
        return $this->getUsers()->contains($user);
    }

    /**
     * @param User $user
     */
    public function setCanShow(User $user): void
    {
        $this->canShow = $this->canShow($user);
    }

    /**
     * @return bool
     */
    public function canAskForProcessingEvaluation($request): bool
    {
        return
            Processing::STATUS_DOING == $this->getStatus() &&
            Processing::STATUS_UNDER_VALIDATION == $request->get('status')
            ;
    }

    /**
     * @return bool
     */
    public function canEmitEvaluatorEvaluation($request): bool
    {
        $new_status = $request->get('evaluation_state');
        $old_status = $this->getEvaluationState();
        return
            null !== $new_status
            &&
            (
                # add an evaluation
                Processing::EVALUATION_STATE_NONE == $old_status &&
                in_array($new_status, [
                    Processing::EVALUATION_STATE_TO_CORRECT,
                    Processing::EVALUATION_STATE_IMPROVABLE,
                    Processing::EVALUATION_STATE_ACCEPTABLE
                ])
                ||
                Processing::EVALUATION_STATE_TO_CORRECT == $old_status &&
                Processing::EVALUATION_STATE_IMPROVABLE == $new_status
                ||
                # remove an evaluation
                Processing::EVALUATION_STATE_IMPROVABLE == $old_status &&
                Processing::EVALUATION_STATE_NONE == $new_status
                ||
                Processing::EVALUATION_STATE_TO_CORRECT == $old_status &&
                Processing::EVALUATION_STATE_NONE == $new_status
            );
    }

    /**
     * @return bool
     */
    public function isUnderValidation(): bool
    {
        return Processing::STATUS_UNDER_VALIDATION;
    }

    /**
     * @return bool
     */
    public function isArchived($request): bool
    {
        $new_status = $request->get('status');
        return
            $new_status == Processing::STATUS_ARCHIVED
            &&
            $new_status != $this->getStatus()
            ;
    }

    /**
     * @return bool
     */
    public function canSubmitPiaToDpo($request): bool
    {
        #FIXME to create!
        return true === $request->get('dpo_submitted_pia');
    }

    /**
     * @return string
     **/
    public function __toString()
    {
        return $this->getName();
    }
}
