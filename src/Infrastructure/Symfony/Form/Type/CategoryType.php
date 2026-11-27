<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Form\Type;

use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\PublicPages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryType extends AbstractType
{
    public function __construct(
        private readonly ?PublicPages $pages = null,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'blog.category.name', 'section' => 'category'])
            ->add('description', TextareaType::class, ['label' => 'blog.category.description', 'required' => false, 'attr' => ['rows' => 4], 'section' => 'category'])
            ->add('slug', TextType::class, [
                'label' => 'blog.slug.label',
                'label_attr' => ['class' => 'visually-hidden'],
                'help' => 'blog.slug.help',
                'required' => false,
                'prefix' => $this->pages?->prefix('category'),
                'section' => 'address',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryResource::class,
            'sections' => [
                'category' => ['label' => 'blog.section.category'],
                'address' => ['label' => 'blog.section.address', 'column' => 'side'],
            ],
        ]);
    }
}
