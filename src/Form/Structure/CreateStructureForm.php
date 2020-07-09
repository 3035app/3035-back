<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Form\Structure;

use Doctrine\Persistence\ManagerRegistry;
use PiaApi\Form\BaseForm;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use PiaApi\Form\Portfolio\Type\PortfolioChoiceType;
use PiaApi\Form\Structure\Type\StructureTypeChoiceType;

class CreateStructureForm extends BaseForm
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label'    => 'pia.structures.forms.create.name',
            ])
            ->add('type', StructureTypeChoiceType::class, [
                'required' => false,
                'label'    => 'pia.structures.forms.create.type',
            ]);

        if (!$options['portfolio']) {
            $builder
                ->add('portfolio', PortfolioChoiceType::class, [
                    'required' => false,
                    'label'    => 'pia.structures.forms.create.portfolio',
                ]);
        } else {
            $builder
                ->add('portfolio', HiddenType::class, [
                    'required'   => true,
                    'data'       => $options['portfolio']->getId(),
                    'data_class' => null,
                ]);
        }

        $builder
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'fluid',
                ],
                'label' => 'pia.structures.forms.create.submit',
            ]);

        if (!$options['portfolio'] && !count($builder->get('portfolio')->getOption('choices'))) {
            $builder->remove('portfolio');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'portfolio' => false,
        ]);
    }
}
