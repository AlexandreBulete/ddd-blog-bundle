<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Form\Type;

use AlexandreBulete\DddBlogBundle\Application\Query\FindCategories\FindCategoriesQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Model\PostStatus;
use AlexandreBulete\DddBlogBundle\Infrastructure\Media\PostVisuals;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\Form\Type\MediaUploadType;
use AlexandreBulete\DddSymfonyBundle\Form\Type\MarkdownType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\PublicPages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PostType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly PublicPages $pages,
        private readonly ?PostVisuals $visuals = null,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'blog.post.title', 'section' => 'content'])
            ->add('excerpt', TextareaType::class, [
                'label' => 'blog.post.excerpt',
                'help' => 'blog.post.excerpt_help',
                'required' => false,
                'attr' => ['rows' => 3],
                'section' => 'content',
            ])
            ->add('body', MarkdownType::class, ['label' => 'blog.post.body', 'required' => false, 'section' => 'content'])
            ->add('status', EnumType::class, [
                'class' => PostStatus::class,
                'label' => 'blog.post.status',
                'choice_label' => static fn (PostStatus $status): string => 'blog.post.status_' . $status->value,
                'section' => 'publication',
            ])
            ->add('publishAt', DateTimeType::class, [
                'label' => 'blog.post.published_at',
                'help' => 'blog.post.published_at_help',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'section' => 'publication',
            ])
            ->add('featured', CheckboxType::class, ['label' => 'blog.post.featured', 'required' => false, 'switch' => true, 'section' => 'publication'])
            ->add('categoryIds', ChoiceType::class, [
                'label' => 'blog.post.categories',
                'help' => 'blog.post.categories_help',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'choices' => $this->categoryChoices(),
                'choice_translation_domain' => false,
                'section' => 'classification',
            ])
            ->add('tags', TextType::class, [
                'label' => 'blog.post.tags',
                'help' => 'blog.post.tags_help',
                'required' => false,
                'section' => 'classification',
            ])
            ->add('slug', TextType::class, [
                'label' => 'blog.slug.label',
                'label_attr' => ['class' => 'visually-hidden'],
                'help' => 'blog.slug.help',
                'required' => false,
                'prefix' => $this->pages->prefix('post'),
                'section' => 'address',
            ]);

        if ($this->visuals !== null) {
            $this->addVisual($builder);
        }
    }

    /**
     * The upload field, with the current visual shown above it, and a box to
     * take it off when there is one.
     */
    private function addVisual(FormBuilderInterface $builder): void
    {
        $builder->add('visualFile', MediaUploadType::class, [
            'label' => 'blog.post.visual',
            'label_attr' => ['class' => 'visually-hidden'],
            'help' => 'blog.post.visual_help',
            'section' => 'visual',
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $post = $event->getData();
            if (!$post instanceof PostResource || $post->visualUrl === null) {
                return;
            }
            $event->getForm()
                ->add('visualFile', MediaUploadType::class, [
                    'label' => 'blog.post.visual',
                    'label_attr' => ['class' => 'visually-hidden'],
                    'help' => sprintf('<img src="%s" alt="" style="display:block;max-width:100%%;max-height:180px;border-radius:4px;margin:0 0 12px">', htmlspecialchars($post->visualUrl, \ENT_QUOTES)),
                    'help_html' => true,
                    'section' => 'visual',
                ])
                ->add('removeVisual', CheckboxType::class, ['label' => 'blog.post.remove_visual', 'required' => false, 'section' => 'visual']);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostResource::class,
            'sections' => [
                'content' => ['label' => 'blog.section.content'],
                'publication' => ['label' => 'blog.section.publication', 'column' => 'side'],
                'visual' => ['label' => 'blog.section.visual', 'column' => 'side'],
                'classification' => ['label' => 'blog.section.classification', 'column' => 'side'],
                'address' => ['label' => 'blog.section.address', 'column' => 'side'],
            ],
        ]);
    }

    /**
     * @return array<string, string> name => id
     */
    private function categoryChoices(): array
    {
        $choices = [];
        /** @var iterable<Category> $categories */
        $categories = $this->queryBus->ask(new FindCategoriesQuery(withSorting: ['name' => 'asc']));
        foreach ($categories as $category) {
            $choices[$category->name] = (string) $category->id;
        }

        return $choices;
    }
}
