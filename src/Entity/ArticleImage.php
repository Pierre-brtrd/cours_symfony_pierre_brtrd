<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Controller\Images\CreateImageController;
use App\Repository\ArticleImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(operations: [
    new Get(),
    new Delete(
        security: 'is_granted(\'ROLE_ADMIN\')',
        securityMessage: 'Sorry, but you don\'t have the rights to do this.',
        openapiContext: [
            'summary' => 'Delete an image',
            'description' => '# Delete Image

You can delete an image but **you have to be admin user**.',
        ]
    ),
    new GetCollection(),
    new Post(controller: CreateImageController::class, deserialize: false, validationContext: [
        'groups' => [
            'Default',
            'image_object_create',
        ],
    ], openapiContext: [
        'requestBody' => [
            'content' => [
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'imageFile' => ['type' => 'string', 'format' => 'binary'],
                            'article' => ['type', 'integer', 'format' => 'id'],
                        ],
                    ],
                    'example' => [
                        '@context' => 'string',
                        '@id' => 'string',
                        '@type' => 'string',
                        'imageFile' => 'file',
                        'article' => '/api/articles/1',
                    ],
                ],
            ],
        ],
    ]),
], types: ['https://schema.org/MediaObject'], normalizationContext: ['groups' => ['image:read']], paginationItemsPerPage: 20)]
#[ORM\Entity(repositoryClass: ArticleImageRepository::class)]
#[Vich\Uploadable]
class ArticleImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(inversedBy: 'articleImages', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['image:post'])]
    private ?Article $article = null;
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     */
    #[Vich\UploadableField(mapping: 'articles_image', fileNameProperty: 'imageName', size: 'imageSize')]
    #[Assert\NotNull(groups: ['image_object_create'])]
    #[Groups(['image:post'])]
    private ?File $imageFile = null;
    #[ORM\Column(length: 255)]
    private ?string $imageName = null;
    #[ApiProperty(iris: ['https://schema.org/contentUrl'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['image:read', 'article:list'])]
    public ?string $imageUrl = null;
    #[ORM\Column]
    private ?int $imageSize = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
}
