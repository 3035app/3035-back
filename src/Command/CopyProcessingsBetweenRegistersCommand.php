<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use PiaApi\Entity\Pia\Structure;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\Folder;

class CopyProcessingsBetweenRegistersCommand extends Command
{
    const NAME = 'pia:processing:cross-copy';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Copy processings, PIAs and linked folders into another register.')
            ->setHelp('This command allows you to copy processings, PIAs and linked folders into another register.')
            ->addArgument('processing-id', InputArgument::REQUIRED, 'The ID of the processing to copy, or IDs separated by a comma.')
            ->addArgument('structure', InputArgument::REQUIRED, 'The target structure (name or ID) where to copy the processing and its folders.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do the import without persisting values in database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $dryRun = $input->getOption('dry-run');
        $structureNameOrId = $input->getArgument('structure');

        // Structure
        $structure = $this->fetchStructure($structureNameOrId);

        if ($structureNameOrId !== null && $structure === null) {
            $this->io->error(sprintf('Cannot find structure with name or id « %s », aborting', $structureNameOrId));
            return;
        }

        // Processings
        foreach ( $this->fetchIds($input->getArgument('processing-id')) as $id ) {
            $processing = $this->fetchProcessing($id);
            if ( $processing == null ) {
                $this->io->warning(sprintf('The processing "%s" is not found.', $id));
                continue;
            }
            $this->copyProcessing($processing, $structure);
        }
    }

    private function copyProcessing(Processing $processing, Structure $structure): void
    {
        if ($processing->getFolder()->getStructureId() == $structure->getId()) {
            $this->io->warning(sprintf('The processing "%s" (%s) has no need to by copied in the structure "%s" as it is already a part of it.', $processing->getName(), $processing->getId(), $structure->getName()));
            return;
        }

        $processing = $this->clone($processing);
        $this->copyFolder($processing, $structure);
        $this->entityManager->flush();
        $this->io->success(sprintf('The processing "%s" has been copied into the structure "%s" with ID %s.', $processing->getName(), $structure->getName(), $processing->getId()));
    }

    private function copyFolder(Processing $processing, Structure $structure): void
    {
        $folder = $processing->getFolder();
        
        $local_folders = $this->fetchFolders($folder, $structure);
        
        if ( count($local_folders) > 0 ) {
            $processing->setFolder($local_folders[0]);
            return;
        }
        
        $processing->setFolder($this->clone($folder));
        $processing->getFolder()->setStructure($structure);
        $this->processFolder($processing->getFolder(), $structure);
    }

    private function processFolder(Folder $folder, Structure $structure): void
    {
        $local_folders = $this->fetchFolders($folder->getParent(), $structure);
        if ( count($local_folders) > 0 ) {
            $folder->setParent($local_folders[0]);
            return;
        }
        
        $folder->setParent($this->clone($folder->getParent()));
        $folder->setStructure($structure);

        if ( $folder->isRoot() ) {
            return;
        }
        
        $this->processFolder($folder->getParent(), $structure);
    }

    private function fetchIds($ids): ?array
    {
        return explode(',', $ids);
    }

    private function fetchProcessing($id): ?Processing
    {
        return $this->entityManager->getRepository(Processing::class)->find($id);
    }

    private function fetchStructure($nameOrId): ?Structure
    {
        return $this->entityManager->getRepository(Structure::class)->findOneByNameOrId($nameOrId);
    }
    
    private function fetchFolders(Folder $folder, Structure $structure): array
    {
        $query = $this->entityManager->createQuery('
            SELECT f
            FROM '.Folder::class.' f
            WHERE f.name = :name AND f.structure = :structure
        ')
        ->setParameter('name', $folder->getName())
        ->setParameter('structure', $structure);

        return $query->getResult();
    }

    private function clone($entity)
    {
        $entity = clone $entity;
        $entity->setUpdatedAt(new \DateTime);
        $this->entityManager->persist($entity);
        return $entity;
    }
}
