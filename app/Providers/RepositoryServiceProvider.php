<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Complaint\ComplaintRepositoryInterface;
use App\Repositories\Complaint\CachedComplaintRepository;
use App\Repositories\Complaint\ComplaintRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            ComplaintRepositoryInterface::class,
            fn () => new CachedComplaintRepository(
                new ComplaintRepository()
            )
        );
    }
}
