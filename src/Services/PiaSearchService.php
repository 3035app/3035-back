<?php


namespace PiaApi\Services;


use Doctrine\ORM\EntityManagerInterface;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\Structure;
use PiaApi\Model\SearchResultModel;
use Symfony\Component\Security\Core\Security;

class PiaSearchService
{
    const STRUCTURE_TYPE = 'structure';
    const FOLDER_TYPE = 'folder';
    const PROCESSING_TYPE = 'processing';

    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function search(string $name)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $name = strtoupper($name);

        $searchModelResults = [];

        if ($this->security->isGranted('CAN_SHOW_PORTFOLIO')) {
            $structures = $this->entityManager->getRepository(Structure::class)->findByNameSearch($name, $user);
            foreach ($structures as $structure) {
                $searchModelResults[] = (new SearchResultModel())
                    ->setId($structure->getId())
                    ->setType(self::STRUCTURE_TYPE)
                    ->setStructureName($structure->getName());
            }
        }

        if ($this->security->isGranted('CAN_SHOW_FOLDER')) {
            $folders = $this->entityManager->getRepository(Folder::class)->findByNameSearch($name, $user);
            foreach ($folders as $folder) {
                $searchModelResults[] = (new SearchResultModel())
                    ->setId($folder->getId())
                    ->setType(self::FOLDER_TYPE)
                    ->setFolderName($folder->getName())
                    ->setStructureName($folder->getStructure()->getName());
            }
        }

        if ($this->security->isGranted('CAN_SHOW_PROCESSING')) {
            $processings = $this->entityManager->getRepository(Processing::class)->findByNameSearch($name, $user);
            foreach ($processings as $processing) {
                $searchModelResults[] = (new SearchResultModel())
                    ->setId($processing->getId())
                    ->setType(self::PROCESSING_TYPE)
                    ->setProcessingName($processing->getName())
                    ->setFolderName($processing->getFolder()->getName())
                    ->setStructureName($processing->getFolder()->getStructure()->getName());
            }
        }

        return $searchModelResults;
    }
}