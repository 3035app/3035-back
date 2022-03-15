<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Structure;
use PiaApi\Services\ApplicationService;
use PiaApi\Repository\StructureRepository;
use Doctrine\Persistence\ObjectManager;
use PiaApi\Services\StructureService;
use PiaApi\Services\StructureTypeService;
use PiaApi\Entity\Oauth\Client;
use PiaApi\Services\UserService;

class PiaApiDefaultTest extends WebTestCase
{
   private $application;
   private $manager;
   private $structure;
   private $users;

   public function testInitializeFixture()
   {
      # Arrange
      self::bootKernel();
      $this->manager = self::$container->get('doctrine')->getManager();
      $this->application = $this->piaApplication(self::$container);
      $this->structure = $this->piaStructure(self::$container);
      $this->users = $this->piaUsers(self::$container);

      # Act
      // $this->manager->refresh($structure);
      // $repository->add($testWeatherQuery1);

      # Assert
      # users array is on the form: [[<user:id> => <user:username>], ...]
      $this->assertEquals(count($this->_users()), count($this->users));
   }

   public function testHello ()
   {
      print_r('hello');
      $this->assertEquals(2, 1+1);
   }

   public function piaApplication($container): Client
   {
      // Arrange
      $applicationService = $container->get(ApplicationService::class);

      // Act
      $application = $this->manager
            ->getRepository(Client::class)
            ->findOneByName('Test-app')
            ;
      if (null == $application) {
         $application = $applicationService->createApplication('Test-app', '');
         $this->manager->persist($application);
         $this->manager->flush();
      }

      // Assert
      $this->assertEquals('Test-app', $application->getName());
      $this->assertEquals('', $application->getUrl());
      return $application;
   }

   public function piaStructure($container): Structure
   {
      // Arrange
      $structureService = $container->get(StructureService::class);
      $structureTypeService = $container->get(StructureTypeService::class);

      // Act
      $structure = $this->manager
            ->getRepository(Structure::class)
            ->findOneByName('Test-struct')
            ;
      if (null == $structure) {
         $structureType = $structureTypeService->createStructureType('Test-St1-type');
         $structure = $structureService->createStructure('Test-struct', $structureType);
         $this->manager->persist($structureType);
         $this->manager->persist($structure);
         $this->manager->flush();
      }

      // Assert
      $this->assertEquals('Test-struct', $structure->getName());
      $this->assertEquals('Test-St1-type', $structure->getType()->getName());
      return $structure;
   }

   public function piaUsers($container): array
   {
      // Arrange
      $userService = $container->get(UserService::class);

      // Act
      $users = [];
      foreach ($this->_users() as $u) {
         $user = $this->manager
            ->getRepository(User::class)
            ->findOneByEmail($u['email'])
            ;
         if (null == $user) {
            $user = $userService->createUser(
               $u['email'],
               $u['password'],
               $this->structure,
               $this->application
               );
            $user->addRole($u['role']);
            $this->manager->persist($user);
            $this->manager->flush();
         }

         // Assert
         $this->assertEquals($u['email'], $user->getEmail());
         $this->assertTrue(in_array($u['role'], $user->getRoles()));
         $users[$user->getId()] = $user->getUsername();
      }
      return $users;
   }

   private function _users(): array
   {
      return [
         ['email' => 'Emmanuel@Eval.Eval', 'password' => 'Emmanuel', 'structure' => 'Test-struct', 'role' => 'ROLE_EVALUATOR'],
         ['email' => 'Robert@Red.Red', 'password' => 'Robert', 'structure' => 'Test-struct', 'role' => 'ROLE_REDACTOR'],
         ['email' => 'Steeve@Red.Red', 'password' => 'Steeve', 'structure' => 'Test-struct', 'role' => 'ROLE_REDACTOR'],
         ['email' => 'Lucas@Red.Red', 'password' => 'Lucas', 'structure' => 'Test-struct', 'role' => 'ROLE_REDACTOR'],
         ['email' => 'David@dpo.dpo', 'password' => 'David', 'structure' => 'Test-struct', 'role' => 'ROLE_DPO']
      ];
   }
}
