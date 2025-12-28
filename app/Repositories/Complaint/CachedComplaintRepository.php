<?php

namespace App\Repositories\Complaint;

use Illuminate\Support\Facades\Cache;
use App\Repositories\Complaint\ComplaintRepositoryInterface;

class CachedComplaintRepository implements ComplaintRepositoryInterface
{
    public function __construct(private ComplaintRepositoryInterface $repository) {}

    public function getUserComplaints(int $userId): array{
        return Cache::remember(
            "user_{$userId}_complaints",
            300,
            fn () => $this->repository->getUserComplaints($userId)
        );
    }

    public function getComplaintDetails(int $complaintId): array{
        return Cache::remember(
            "complaint_details_{$complaintId}",
            300,
            fn () => $this->repository->getComplaintDetails($complaintId)
        );
    }

    public function getComplaintTypes(): array{
        return Cache::remember(
            'complaint_types',
            86400,
            fn () => $this->repository->getComplaintTypes()
        );
    }

    public function clearUserComplaintsCache(int $userId): void{
        Cache::forget("user_{$userId}_complaints");
    }

    public function clearComplaintDetailsCache(int $complaintId): void{
        Cache::forget("complaint_details_{$complaintId}");
    }
}
