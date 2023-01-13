<?php

namespace App\Services\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;

interface ProviderServiceInterface
{
    public function redirect(): RedirectResponse;
    public function callback($data): string;
}
