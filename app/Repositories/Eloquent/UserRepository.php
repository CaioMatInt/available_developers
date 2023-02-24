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

    public function findByExternalProviderId(string $userExternalProviderId): ?User
    {
        return $this->model->where('external_provider_id', $userExternalProviderId)->first();
    }

    //@@TODO: finish implementing this method
    public function update(int $id, array $data): User
    {
        $user = $this->model->find($id);
        $user->update($data);

        return $user;
    }
}
