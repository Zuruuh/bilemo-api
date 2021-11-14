<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class UserFormType extends AbstractType
{
    public const NAME_MAX_LENGTH = 32;
    public const NAME_MISSING_MESSAGE = 'Your have to specify a name !';
    public const NAME_MAX_LENGTH_MESSAGE = 'User\'s name cannot be longer than ' . self::NAME_MAX_LENGTH . ' characters';

    public const BALANCE_MISSING_MESSAGE = 'You have to specify a balance !';
    public const BALANCE_INVALID_MESSAGE = 'Balance ({{ value }}) must be a valid integer';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => self::NAME_MISSING_MESSAGE
                    ]),
                    new Length([
                        'max' => self::NAME_MAX_LENGTH,
                        'maxMessage' => self::NAME_MAX_LENGTH_MESSAGE
                    ]),
                ]
            ])
            ->add('balance', IntegerType::class, [
                'empty_data' => 0,
                'invalid_message' => self::BALANCE_INVALID_MESSAGE,
                'constraints' => [
                    new Type([
                        'type' => 'integer',
                        'message' => self::BALANCE_INVALID_MESSAGE
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
