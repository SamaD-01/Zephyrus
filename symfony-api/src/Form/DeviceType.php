<?php

namespace App\Form;

use App\Entity\Device;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Device Name',
                'attr' => [
                    'placeholder' => 'e.g., Living Room Sensor'
                ],
                'constraints' => [
                    new NotBlank(message: 'Please enter a device name'),
                ],
            ])
            ->add('deviceId', TextType::class, [
                'label' => 'Device ID',
                'attr' => [
                    'placeholder' => 'e.g., sensor-001'
                ],
                'constraints' => [
                    new NotBlank(message: 'Please enter a device ID'),
                    new Regex(
                        pattern: '/^[a-zA-Z0-9_-]+$/',
                        message: 'Device ID can only contain letters, numbers, hyphens and underscores'
                    ),
                ],
                'help' => 'Unique identifier for MQTT communication (letters, numbers, - and _ only)'
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., Living Room, Office'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Optional notes about this device',
                    'rows' => 3
                ]
            ])
            ->add('maxTemperature', NumberType::class, [
                'label' => 'Max Temperature Alert (°C)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., 30',
                    'step' => '0.1'
                ],
                'help' => 'Alert when temperature exceeds this value (leave empty for default: 30°C)'
            ])
            ->add('minTemperature', NumberType::class, [
                'label' => 'Min Temperature Alert (°C)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., 15',
                    'step' => '0.1'
                ],
                'help' => 'Alert when temperature falls below this value'
            ])
            ->add('maxCo2', NumberType::class, [
                'label' => 'Max CO₂ Alert (ppm)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., 1000'
                ],
                'help' => 'Alert when CO₂ exceeds this value (leave empty for default: 1000ppm)'
            ])
            ->add('maxNoise', NumberType::class, [
                'label' => 'Max Noise Alert (dB)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., 70',
                    'step' => '0.1'
                ],
                'help' => 'Alert when noise exceeds this value (leave empty for default: 70dB)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
        ]);
    }
}