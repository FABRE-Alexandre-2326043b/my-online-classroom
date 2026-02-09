<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\QuizResult;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        $teachers = [];
        for ($i = 1; $i <= 2; $i++) {
            $teacher = new User();
            $teacher->setEmail("prof$i@test.com");
            $teacher->setRoles(['ROLE_TEACHER']);
            $teacher->setPassword($this->hasher->hashPassword($teacher, 'password'));
            $manager->persist($teacher);
            $teachers[] = $teacher;
        }

        $students = [];
        
        $mainStudent = new User();
        $mainStudent->setEmail('eleve@test.com');
        $mainStudent->setRoles(['ROLE_USER']);
        $mainStudent->setPassword($this->hasher->hashPassword($mainStudent, 'password'));
        $manager->persist($mainStudent);
        $students[] = $mainStudent;

        for ($i = 0; $i < 10; $i++) {
            $student = new User();
            $student->setEmail($faker->email());
            $student->setRoles(['ROLE_USER']);
            $student->setPassword($this->hasher->hashPassword($student, 'password'));
            $manager->persist($student);
            $students[] = $student;
        }
        
        $courses = [];
        foreach ($teachers as $teacher) {
            for ($j = 0; $j < mt_rand(3, 5); $j++) {
                $course = new Course();
                $course->setTitle($faker->sentence(4));
                $course->setDescription($faker->paragraphs(3, true)); 
                $course->setTeacher($teacher);
                
                $manager->persist($course);
                $courses[] = $course;
            }
        }


        foreach ($courses as $course) {
            if (mt_rand(0, 1)) {
                $quiz = new Quiz();
                $quiz->setTitle('QCM : ' . $course->getTitle());
                $quiz->setCourse($course);

                $questions = [];
                for ($q = 1; $q <= 5; $q++) {
                    $questions[] = [
                        'question' => $faker->sentence(6) . ' ?',
                        'options' => [
                            $faker->word(),
                            $faker->word(),
                            $faker->word(),
                            $faker->word()
                        ],
                        'correct_index' => mt_rand(0, 3) 
                    ];
                }
                $quiz->setQuestions($questions);

                $manager->persist($quiz);

                foreach ($students as $student) {
                    if (mt_rand(1, 100) <= 30) {
                        $result = new QuizResult();
                        $result->setStudent($student);
                        $result->setQuiz($quiz);
                        $result->setScore(mt_rand(5, 20));
                        $result->setCompletedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));
                        
                        $manager->persist($result);
                    }
                }
            }
        }

        $manager->flush();
    }
}