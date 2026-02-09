<?php

namespace App\State;

use App\Entity\QuizResult;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class QuizSubmissionProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof QuizResult) {
            return;
        }

        $user = $this->security->getUser();
        $data->setStudent($user);
        $data->setCompletedAt(new \DateTimeImmutable());

        $quiz = $data->getQuiz();
        $questions = $quiz->getQuestions();
        $studentAnswers = $data->userAnswers;
        
        $score = 0;
        $total = count($questions);

        foreach ($questions as $index => $q) {
            if (isset($studentAnswers[$index]) && $studentAnswers[$index] == $q['correct_index']) {
                $score++;
            }
        }

        $finalScore = ($total > 0) ? round(($score / $total) * 20) : 0;
        $data->setScore($finalScore);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}