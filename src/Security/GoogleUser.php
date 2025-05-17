<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class GoogleUser implements UserInterface // Serializable is often not strictly needed if all properties are simple types
{
    private string $email;
    private array $roles = [];

    public function __construct(string $email, array $roles = ['ROLE_USER'])
    {
        $this->email = $email;
        $this->roles = $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        // This user is authenticated via Google, no local password
        return null;
    }

    public function getSalt(): ?string
    {
        // Not needed when not using a password
        return null;
    }

    public function eraseCredentials(): void
    {
        // No sensitive data to clear
    }

    // Optional: If you store more complex data or want explicit control
    // you can implement __serialize and __unserialize (PHP 7.4+)
    // or implement Serializable for older PHP versions (though usually not needed for simple DTOs like this)

    public function __serialize(): array
    {
        return [
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->email = $data['email'];
        $this->roles = $data['roles'];
    }
}