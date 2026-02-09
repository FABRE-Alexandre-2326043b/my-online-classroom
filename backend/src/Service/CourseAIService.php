<?php

namespace App\Service;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Chat\Chat;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Chat\InMemory\Store as InMemoryMessageStore;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseAIService
{
    public function __construct(
        private AgentInterface $agent
    ) {}

    public function generateCourseDescription(string $pdfContent, string $courseTitle): string
    {
        $cleanContent = substr($pdfContent, 0, 20000);

        $prompt = sprintf(
            "Voici le contenu d'un cours intitulé '%s'. \n\n" .
            "CONTENU DU COURS :\n%s\n\n" .
            "Tâche : Rédige une description concise, engageante et structurée de ce cours pour des étudiants.",
            $courseTitle,
            $cleanContent
        );

        $chat = new Chat($this->agent, new InMemoryMessageStore());
        $response = $chat->submit(Message::ofUser($prompt));
        
        return $response->getContent();
    }

    public function generateQuiz(string $courseContent, int $questions = 5, string $type = 'qcm'): array
    {
        if ($type === 'true_false') {
            $instruction = "Génère $questions questions de type 'Vrai ou Faux'. 
            Pour chaque question, le tableau 'options' doit être exactement ['Vrai', 'Faux'].
            'correct_index' doit être 0 pour Vrai ou 1 pour Faux.";
        } else {
            $instruction = "Génère $questions questions de type QCM (Choix Multiples).
            Chaque question doit avoir 4 choix de réponse distincts dans 'options'.";
        }
    
        $cleanContent = substr($courseContent, 0, 20000);

        $prompt = sprintf(
            "Crée un QCM de %d questions basé sur le texte suivant.\n" .
            "TEXTE DU COURS : %s\n\n" .
            "RÈGLES IMPORTANTES :\n" .
            "1. Retourne UNIQUEMENT un tableau JSON valide.\n" .
            "2. Format attendu : [{\"question\": \"...\", \"options\": [\"Reponse A\", \"Reponse B\", \"Reponse C\"], \"correct_index\": 0}]\n" .
            "3. 'correct_index' est l'index de la bonne réponse dans le tableau options (0, 1 ou 2).\n" .
            "4. Ne mets pas de Markdown (pas de ```json).",
            $questions,
            $cleanContent
        );

        $chat = new Chat($this->agent, new InMemoryMessageStore());
        $response = $chat->submit(Message::ofUser($prompt));
        
        $content = $response->getContent();
        
        $content = str_replace(['```json', '```'], '', $content);
        
        // Décodage du JSON
        $quizData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("L'IA a généré un JSON invalide : " . $content);
        }

        return $quizData;
    }
}