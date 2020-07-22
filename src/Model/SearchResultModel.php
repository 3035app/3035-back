<?php

/*
 * Copyright (C) 2015-2019 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Model;

use JMS\Serializer\Annotation as JMS;

class SearchResultModel
{

    /**
     * @JMS\Groups({"Default"})
     * @var int
     */
    private $id;

    /**
     * @JMS\Groups({"Default"})
     * @var string
     */
    private $type;

    /**
     * @JMS\Groups({"Default"})
     * @var string
     */
    private $structureName;

    /**
     * @JMS\Groups({"Default"})
     * @var string|null
     */
    private $folderName;

    /**
     * @JMS\Groups({"Default"})
     * @var string|null
     */
    private $processingName;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SearchResultModel
     */
    public function setId(int $id): SearchResultModel
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SearchResultModel
     */
    public function setType(string $type): SearchResultModel
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStructureName(): string
    {
        return $this->structureName;
    }

    /**
     * @param string $structureName
     * @return SearchResultModel
     */
    public function setStructureName(string $structureName): SearchResultModel
    {
        $this->structureName = $structureName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFolderName(): ?string
    {
        return $this->folderName;
    }

    /**
     * @param string|null $folderName
     * @return SearchResultModel
     */
    public function setFolderName(?string $folderName): SearchResultModel
    {
        $this->folderName = $folderName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProcessingName(): ?string
    {
        return $this->processingName;
    }

    /**
     * @param string|null $processingName
     * @return SearchResultModel
     */
    public function setProcessingName(?string $processingName): SearchResultModel
    {
        $this->processingName = $processingName;
        return $this;
    }
}