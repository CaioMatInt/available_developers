<?php

namespace App\Jobs;

use App\Events\UserImageUpdatedEvent;
use App\Repositories\Eloquent\UserRepository;
use App\Services\FileUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadFromUrlAndUpdateUserImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private FileUploadService $fileUploadService,
        private string $avatarUrl,
        private int $userId,
        private UserRepository $userRepository
    ) { }

    public function handle()
    {
        $fileUrl = $this->fileUploadService
            ->downloadFileFromUrlAndUploadIt($this->avatarUrl,'users/avatars', $this->userId);

        if ($fileUrl) {
            //@@TODO: ImplementEvent
            $this->userRepository->update($this->userId, ['image' => $fileUrl]);
            UserImageUpdatedEvent::dispatch();
        } else {
            throw new \Exception('Could not download file from url');
        }
    }

    public function failed(
        /*@@TODO implement failed to uploadi mage event*/
    ){}
}
