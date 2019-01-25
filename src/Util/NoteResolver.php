<?php

namespace App\Util;

use App\Entity\Presentation;
use App\Repository\CommentRepository;

class NoteResolver{

        private $commentRepo = null;

        function __construct(CommentRepository $commentRepo)
        {
            $this->commentRepo = $commentRepo;
        }

        public function getUserNoteFromPres(Presentation $pres)
        {
            $comments = $this->commentRepo->findBy(['petsitter' => $pres->getUser()->getId()]);
            $commentsCount = count($comments);

            // On initialise la variable d'addition
            $sum = 0;

            foreach($comments as $currentComment)
            {
                $sum += $currentComment->getNote();
            }

            if($commentsCount > 0)
            {
                $moy = $sum / $commentsCount;
            }
            else
            {
                $moy = 'NC';
            }
            
            return $moy;
        }

        public function getUsersNotesFromPres($presList)
        {
            $notes = [];

            foreach($presList as $pres)
            {
                $notes[$pres->getUser()->getId()] = $this->getUserNoteFromPres($pres);
            }

            return $notes;
        }
}