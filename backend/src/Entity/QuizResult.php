<?php

namespace App\Entity;

use App\Repository\QuizResultRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\QuizSubmissionProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: QuizResultRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['result:read']],
            security: "is_granted('ROLE_USER')"
        ),
        new Get(
            normalizationContext: ['groups' => ['result:read']],
            security: "is_granted('ROLE_USER') and object.getStudent() == user"
        ),
        new Post(
            denormalizationContext: ['groups' => ['result:write']],
            normalizationContext: ['groups' => ['result:read']],
            processor: QuizSubmissionProcessor::class,
            security: "is_granted('ROLE_USER')"
        )
    ],
    normalizationContext: ['groups' => ['result:read']],
    denormalizationContext: ['groups' => ['result:write']]
)]

#[ApiFilter(SearchFilter::class, properties: ['student' => 'exact'])]
class QuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['result:read'])]
    private ?int $score = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['result:read', 'result:write'])]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['result:read'])]
    private ?User $student = null;

    #[Groups(['result:write'])]
    public array $userAnswers = []; 

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
