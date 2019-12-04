<?php

/*
 * Copyright (C) 2015-2019 Baptiste LARVOL-SIMON
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
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Processing;
use Cocur\Slugify\Slugify;
use Alom\Graphviz\Digraph;

class GenerateCartographyCommand extends Command
{
    const NAME = 'pia:cartography';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;
    
    /**
     * @var Slugify
     */
    protected $slug;
    
    private $fontSizes;
    private $structureNameOrId;
    
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->slug = new Slugify;
        $this->setFontSizes();
        $this->structureNameOrId = null;
    }
    
    public function setFontSizes(array $sizes = [36, 24, 20, 16])
    {
        $this->fontSizes = $sizes;
    }
    public function getFontSizes()
    {
        return $this->fontSizes;
    }
    public function getFontSize($lvl)
    {
        return array_key_exists($lvl, $this->fontSizes) ? $this->fontSizes[$lvl] : null;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generates the cartography of processings in a DOT format (graphviz).')
            ->setHelp('This command allows you to generate a cartography of processing based on the existing database')
            ->addOption('structure', null, InputOption::VALUE_OPTIONAL, 'The target structure (name or ID) where to put the focus of the graph')
            ->addOption('folder', null, InputOption::VALUE_OPTIONAL, 'The target folder (name or ID) where to start the graph, within the structure')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->structureNameOrId = $input->getOption('structure');
        $folderNameOrId = $input->getOption('folder');

        // Structure

        $structure = $this->fetchStructure($this->structureNameOrId);

        if ($this->structureNameOrId !== null && $structure === null) {
            $this->io->error(sprintf('Cannot find structure with name or id « %s », aborting', $this->structureNameOrId));

            return 1;
        }

        // Transforming from json to entities
        //$data = $this->fetchDataAsEntities($fileContent, $structure);
        
        $folders = $this->organizeData($structure);
        $folders = $this->moveRootFolder($folderNameOrId, $folders);
        if ( !$folders ) {
            return 2;
        }
        
        echo $this->generateGraphviz($folders)->render();
        return 0;
    }
    
    private function generateGraphviz($folders, $root = null, Digraph $graph = null)
    {
        if ( $graph === null ) {
            $graph = new Digraph('G');
        }
        
        // nodes
        foreach ( $folders as $folder ) {
            // drawing the folder itself
            $this->addFolderTo($graph, $folder, $root);
            
            // drawing processings
            foreach ( $folder['processings'] as $processing ) {
              $this->addProcessingTo($graph, $processing, $folder);
            }
             
            // recursive call for subfolders
            if ( $folder['folders'] ) {
                $this->generateGraphviz($folder['folders'], $folder, $graph);
            }
        }
        
        return $graph;
    }
    
    private function addFolderTo(Digraph $graph, $folder, $root)
    {
        $graph->node($folder['slug'], ['label' => $folder['name'], 'shape' => 'box', 'fontsize' => $this->getFontSize($folder['lvl'])]);
        if ( $root ) {
            $this->addLinkTo($graph, $root, $folder);
        }
    }
    private function addProcessingTo(Digraph $graph, $processing, $folder)
    {
        if ( false && trim($processing['purposes']) ) {
            // needs to be implemented correcting to embed HTML
            $graph->node($processing['slug'], ['shape' => 'Mrecord', 'label' => '{'.$processing['name'].'|<'.str_replace("\n", "", $processing['purposes']).'>}', '_escaped' => false]);
        }
        else {
            $graph->node($processing['slug'], ['label' => $processing['name']]);
        }
        $this->addLinkTo($graph, $folder, $processing);
        return $this;
    }
    private function addLinkTo(Digraph $graph, $orig, $dest, $oriented = false)
    {
        $graph->edge([$orig['slug'], $dest['slug']], ['dir' => $oriented ? '' : 'none']);
        return $this;
    }
    
    private function moveRootFolder($folderNameOrId, array $folders): ?array
    {
        if ( !$folderNameOrId ) {
            return $folders;
        }
        
        // by id
        if ( intval($folderNameOrId).'' == ''.$folderNameOrId ) {
            if ( array_key_exists($folderNameOrId, $folders) ) {
                return [$folderNameOrId => $folders[$folderNameOrId]];
            }
            foreach ( $folders as $folder ) {
                // exception
                if ( !array_key_exists('folders', $folder) ) {
                    return null;
                }
                
                // subfolders
                $r = $this->moveRootFolder($folderNameOrId, $folder['folders']);
                if ( $r ) {
                    return $r;
                }
            }
            return null;
        }
        
        // by name or slug
        else {
            foreach ( $folders as $id => $folder ) {
                // folder exists in the current list
                if ( $folder['slug'] == $folderNameOrId || $folder['name'] == $folderNameOrId ) {
                    return [$id => $folder];
                }
                
                // exception
                if ( !array_key_exists('folders', $folder) ) {
                    return null;
                }
                
                // subfolders
                $r = $this->moveRootFolder($folderNameOrId, $folder['folders']);
                if ( $r ) {
                    return $r;
                }
            }
            return null;
        }
    }
    
    private function organizeData(Structure $structure)
    {
        $folders = [];
        foreach ( $structure->getFolders() as $folder ) {
            // root dir
            if ( $folder->getLvl() == 0 ) {
                $folders[$folder->getId()] = $this->populateFolder($folder);
                continue;
            }
            
            // build parents path
            $parents = [];
            $parent = $folder;
            while ( $parent = $parent->getParent() ) {
                $parents[] = $parent->getId();
            }
            
            $branch = &$folders;
            while ( $fid = array_pop($parents) ) {
                if ( array_key_exists($fid, $branch) ) {
                    $branch = &$branch[$fid]['folders'];
                }
            }
            
            $branch[$folder->getId()] = $this->populateFolder($folder);
        }
        return $folders;
    }
    
    private function populateFolder(Folder $folder)
    {
        return [
            'slug' => $this->slugify($folder->getName()),
            'name' => $folder->getLvl() == 0 && $this->structureNameOrId ? $this->structureNameOrId : $folder->getName(),
            'lvl'  => $folder->getLvl(),
            'folders' => [],
            'processings' => $this->populateProcessings($folder),
        ];
    }
    
    private function populateProcessings(Folder $folder)
    {
        $r = [];
        foreach ( $folder->getProcessings() as $processing ) {
            $r[$processing->getId()] = [
                'slug' => $this->slugify($processing->getName()),
                'name' => $processing->getName(),
                'purposes' => $processing->getDescription(),
            ];
        }
        return $r;
    }

    private function fetchStructure($nameOrId): ?Structure
    {
        $field = intval($nameOrId).'' === ''.$nameOrId ? 'id' : 'name';
        $q = 'SELECT s, f, p
              FROM PiaApi\Entity\Pia\Structure s
              LEFT JOIN s.folders f
              LEFT JOIN f.processings p
              WHERE s.'.$field.' = :field
              ORDER BY f.lvl, f.parent, f.name, p.name'
        ;
        return $this->entityManager
            ->createQuery($q)
            ->setParameter('field', $nameOrId)
            ->getOneOrNullResult()
        ;
    }
    
    protected function slugify($str)
    {
        return $this->slug->slugify($str);
    }
}
