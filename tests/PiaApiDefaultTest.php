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
use PiaApi\Services\ApplicationService;
use PiaApi\Repository\ApplicationRepository;


class PiaApiDefaultTest extends WebTestCase
{
   public function testInitializeEnv()
   {
      # Arrange
      self::bootKernel();
      $container = self::$container;
      $this->piaApplication($container);

      # Act
      // $repository->add($testWeatherQuery1);

      # Assert
      $this->assertEquals(2, 1+1);
   }

   public function testsAreWorkingTest ()
   {
      print_r('hello');
      $this->assertEquals(2, 1+1);
   }

   public function piaApplication($container)
   {
      // Arrange
      $applicationService = $container->get(ApplicationService::class);
      $repository = $container->get(ApplicationRepository::class);

      // Act
      $application = $applicationService->createApplication('Test-app', '');

      // Assert
      $records = $repository->findAll();
      $this->assertEquals('Test-app', $application->getName());
      $this->assertEquals('', $application->getUrl());
   }

   public function piaStructure(KernelInterface $kernel): Response
   {
      $application = new Application($kernel);
      $application->setAutoExit(false);
      $input = new ArrayInput([
         'command' => 'pia:structure:create',
         'name' => 'Test-struct',
         '--type' => 'Test-St1-type',
         ]);
      $output = new NullOutput();
      $application->run($input, $output);
      return new Response('');
   }

   public function piaUsers(KernelInterface $kernel): Response
   {
      $application = new Application($kernel);
      $application->setAutoExit(false);
      foreach ($this->_users() as $user) {
         $input = new ArrayInput([
            'command' => 'pia:user:create',
            'email' => $user['email'],
            'password' => $user['password'],
            '--structure' => $user['structure'],
            ]);
         $output = new NullOutput();
         $application->run($input, $output);
      }
      return new Response('');
   }

   public function piaRoleUsers(KernelInterface $kernel): Response
   {
      $application = new Application($kernel);
      $application->setAutoExit(false);
      foreach ($this->_users() as $user) {
         $input = new ArrayInput([
            'command' => 'pia:user:promote',
            'email' => $user['email'],
            '--role' => $user['role'],
            ]);
         $output = new NullOutput();
         $application->run($input, $output);
      }
      return new Response('');
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
