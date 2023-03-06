<?php

namespace App\Jobs;

use App\Events\User\UserFailedImageUpdatedEvent;
use App\Events\User\UserImageUpdatedEvent;
use App\Repositories\Eloquent\UserRepository;
use App\Services\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadAndUpdateUserImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private FileService    $fileService,
        private string         $userImage,
        private int            $userId,
        private UserRepository $userRepository
    ) { }

    public function handle()
    {
        $downloadedFile = file_get_contents($this->userImage);

        if (!$downloadedFile) {
            throw new \Exception('Could not download file from url');
        }

        $fileUrl = $this->fileService->save($downloadedFile,'users/images', $this->userId);

        if (!$fileUrl) {
            throw new \Exception('Could not upload file');
        }

        $this->userRepository->update($this->userId, ['image' => $fileUrl]);
        UserImageUpdatedEvent::dispatch();
    }

    public function failed(){
        UserFailedImageUpdatedEvent::dispatch();
    }
}
