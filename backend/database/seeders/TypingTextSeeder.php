<?php

namespace Database\Seeders;

use App\Models\TypingText;
use Illuminate\Database\Seeder;

class TypingTextSeeder extends Seeder
{
    public function run(): void
    {
        TypingText::upsert([
            [
                'content' => 'The quick brown fox jumps over the lazy dog. This pangram contains every letter of the English alphabet at least once. Typing practice is essential for improving your speed and accuracy. Regular practice with varied content helps develop muscle memory and finger dexterity. Challenge yourself with different difficulty levels to track your progress.',
                'language' => 'en',
                'word_count' => 75,
                'difficulty' => 'easy',
                'source_label' => 'Classic Pangram',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'In the realm of digital communication, typing has become an indispensable skill that shapes our interaction with technology. The evolution of keyboard layouts, from QWERTY to alternative designs, reflects ongoing efforts to optimize typing efficiency. Professionals across industries recognize the importance of developing both speed and precision in their typing abilities. Advanced typists often employ specialized techniques to minimize errors while maintaining high WPM rates. Consistent practice and muscle memory development are key factors in achieving typing excellence.',
                'language' => 'en',
                'word_count' => 110,
                'difficulty' => 'medium',
                'source_label' => 'Technology & Typing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'The multifaceted nature of contemporary typing contests encompasses not merely the mechanical aspects of key depression and character generation, but also the psychological dimensions of sustained concentration and performance anxiety management. Competitive typists frequently employ sophisticated training methodologies, including fingerprint rhythm analysis, ergonomic optimization protocols, and cognitive stress-inoculation techniques. The neuromuscular coordination requisite for elite-level typing performance necessitates meticulous attention to postural alignment, wrist positioning, and the development of sophisticated proprioceptive awareness that transcends conventional motor skill acquisition paradigms.',
                'language' => 'en',
                'word_count' => 105,
                'difficulty' => 'hard',
                'source_label' => 'Advanced Typing Concepts',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'Καλημέρα! Η πληκτρολόγηση είναι μια ουσιαστική δεξιότητα στο σύγχρονο κόσμο. Η ανάπτυξη της ταχύτητας πληκτρολόγησης απαιτεί συνεπή εξάσκηση και αφοσίωση. Πολλοί επαγγελματίες χρησιμοποιούν τεχνικές βελτίωσης για να αυξήσουν την παραγωγικότητά τους. Η ακρίβεια είναι εξίσου σημαντική με την ταχύτητα.',
                'language' => 'el',
                'word_count' => 58,
                'difficulty' => 'medium',
                'source_label' => 'Greek Practice Text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'La dactylographie est un art qui combine la vitesse et la précision. Les compétitions de dactylographie modernes exigent une concentration intense et une maîtrise des techniques avancées. Les participants doivent maintenir une posture correcte et développer une mémoire musculaire exceptionnelle. L\'entraînement régulier et la pratique cohérente sont essentiels pour atteindre l\'excellence typographique.',
                'language' => 'fr',
                'word_count' => 72,
                'difficulty' => 'medium',
                'source_label' => 'French Practice Text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['content']);
    }
}
