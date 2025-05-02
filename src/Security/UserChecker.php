<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            // the message will be shown to the user
            throw new CustomUserMessageAuthenticationException('Votre compte n\'est pas encore vérifié. Veuillez confirmer votre adresse email.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // no post-auth checks needed
    }
}
