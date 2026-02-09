<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use \Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['quiz:read']]),
        new GetCollection(normalizationContext: ['groups' => ['quiz:read_collection']]),
    ],
    normalizationContext: ['groups' => ['quiz:read']],
    denormalizationContext: ['groups' => ['quiz:read_collection']]
)]
#[ApiFilter(SearchFilter::class, properties: ['course' => 'exact'])]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['quiz:read', 'quiz:read_collection', 'result:read'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz:read_collection'])]
    private ?Course $course = null;

    /**
     * @var Collection<int, QuizResult>
     */
    #[ORM\OneToMany(targetEntity: QuizResult::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $quizResults;

    #[ORM\Column]
    private array $questions = [];

    public function __construct()
    {
        $this->quizResults = new ArrayCollection();
    }

    #[Groups(['quiz:read'])]
    public function getPublicQuestions(): array
    {
        return array_map(function($q) {
            if (isset($q['correct_index'])) {
                unset($q['correct_index']);
            }
            return $q;
        }, $this->questions);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection<int, QuizResult>
     */
    public function getQuizResults(): Collection
    {
        return $this->quizResults;
    }

    public function addQuizResult(QuizResult $quizResult): static
    {
        if (!$this->quizResults->contains($quizResult)) {
            $this->quizResults->add($quizResult);
            $quizResult->setQuiz($this);
        }

        return $this;
    }

    public function removeQuizResult(QuizResult $quizResult): static
    {
        if ($this->quizResults->removeElement($quizResult)) {
            // set the owning side to null (unless already changed)
            if ($quizResult->getQuiz() === $this) {
                $quizResult->setQuiz(null);
            }
        }

        return $this;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): static
    {
        $this->questions = $questions;

        return $this;
    }
}
