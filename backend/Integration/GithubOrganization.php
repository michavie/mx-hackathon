<?php

namespace App\Domain\Integration;

class GithubOrganization
{
    public function __construct(
        public string $name,
        public string $avatarUrl,
    ) {
    }
}
