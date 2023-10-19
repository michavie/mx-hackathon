<?php

namespace App\Domain\Integration;

class GithubRepository
{
    public function __construct(
        public string $id,
        public string $name,
        public string $fullName,
        public bool $private,
    ) {

    }
}
