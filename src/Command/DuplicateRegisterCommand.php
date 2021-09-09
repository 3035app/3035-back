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

class DuplicateRegisterCommand extends Command
{
    const NAME = 'pia:processing:duplicate-register';

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
            ->setDescription('Copy a register into another register.')
            ->setHelp('This command allows you to copy processings, PIAs and linked folders from a register into an other.')
            ->addArgument('source', InputArgument::REQUIRED, 'The source structure (name or ID) where to find processings & register.')
            ->addArgument('destination', InputArgument::REQUIRED, 'The target structure (name or ID).')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do the import without persisting values in database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $dryRun = $input->getOption('dry-run');
        $sourceId = $input->getArgument('source');
        $destId = $input->getArgument('destination');

        // Structures
        $structures = [];
        $structures['source'] = $this->fetchStructure($sourceId);
        $structures['dest'] = $this->fetchStructure($destId);

        if ( $structures['source'] == null ) {
            $this->io->error(sprintf('Cannot find the source structure with name or id « %s », aborting', $sourceId));
            return;
        }
        if ( $structures['dest'] == null ) {
            $this->io->error(sprintf('Cannot find the destination structure with name or id « %s », aborting', $destId));
            return;
        }

        // Find root folders
        $roots = ['source' => null, 'dest' => null];
        foreach ( $roots as $structure => $root ) {
            $roots[$structure] = $this->findRootFolder($structures[$structure]);
        }

        foreach ( $roots as $type => $folder ) {
            echo $type.': '.$folder->getId();
        }

        // Process records starting with root folder
        $this->processFolder($roots['source'], $roots['dest']);

        // Record everything
        $this->entityManager->flush();
    }

    private function processFolder(Folder $source, Folder $dest)
    {
        // Processings
        foreach ( $source->getProcessings() as $processing ) {
            $this->processProcessing($processing, $dest);
        }

        // Other folders
        $folders = ['source' => [], 'dest' => []];
        foreach ( $dest->getChildren() as $folder ) {
            $folders['dest'][$folder->getId()] = $folder->getName();
        }
        foreach ( $source->getChildren() as $folder ) {
            $newFolder = $this->clone($folder);
            $newFolder->setStructure($dest->getStructure());
            $newFolder->setParent($dest);

            // avoid 2 folders with the same name, one legacy & one just created
            if ( in_array($folder->getName(), $folders['dest']) ) {
                $newFolder->setName($newFolder->getName().' - NEW.'.date('YmdHis'));
            }

            $this->persist($newFolder);
            $this->processFolder($folder, $newFolder);
        }
    }

    private function processProcessing(Processing $processing, Folder $folder) {
        $newProcessing = $this->clone($processing);

        $newProcessing->setFolder($folder);

        // Comments
        foreach ( $processing->getComments() as $comment ) {
            $newProcessing->addComment($this->clone($comment));
        }

        // ProcessingDataTypes
        foreach ( $processing->getProcessingDataTypes() as $pdt ) {
            $newProcessing->addProcessingDataType($this->clone($pdt));
        }

        // Attachments
        foreach ( $processing->getAttachments() as $attachment ) {
            $newProcessing->addAttachment($this->clone($attachment));
        }

        // Pias
        foreach ( $processing->getPias() as $pia ) {
            $this->processPia($pia, $newProcessing);
        }
    }

    private function processPia(Pia $pia, Processing $processing) {
        $newPia = $this->clone($pia);
        $newPia->setStructure($processing->getFolder()->getStructure());
        $newPia->setProcessing($processing);

        // Answers
        foreach ( $pia->getAnswers() as $answer ) {
            $newPia->addAnswer($this->clone($answer));
        }

        // Comments
        foreach ( $pia->getComments() as $comment ) {
            $newPia->addComment($this->clone($comment));
        }

        // Evaluations
        foreach ( $pia->getEvaluations() as $evaluation ) {
            $newPia->addEvaluation($this->clone($evaluation));
        }

        // Measures
        foreach ( $pia->getMeasures() as $measure ) {
            $newPia->addMeasure($this->clone($measure));
        }

        // Attachments
        foreach ( $pia->getAttachments() as $attachment ) {
            $newPia->addAttachment($this->clone($attachment));
        }
    }

    private function findRootFolder(Structure $structure): ?Folder
    {
        $folders = $structure->getFolders();
        foreach ( $folders as $folder ) {
            if ( $folder->getParent() === null ) {
                return $folder;
            }
        }
        return null;
    }

    private function fetchStructure($nameOrId): ?Structure
    {
        return $this->entityManager->getRepository(Structure::class)->findOneByNameOrId($nameOrId);
    }

    private function persist($entity)
    {
        $this->entityManager->persist($entity);
        return $entity;
    }

    private function clone($entity)
    {
        $entity = clone $entity;

        if ( method_exists($entity, 'setUpdatedAt') ) {
            $entity->setUpdatedAt(new \DateTime);
        }

        return $this->persist($entity);
    }
}
