<?php

namespace App\Repositories\Eloquent;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function getAuthenticatedUser(): User
    {
        return auth()->user();
    }

    public function findByProviderId(string $userProviderId): ?User
    {
        return $this->model->where('provider_id', $userProviderId)->first();
    }
}
