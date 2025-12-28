<?php

namespace App\Repositories\Complaint;

interface ComplaintRepositoryInterface
{
    public function getUserComplaints(int $userId): array;
    public function getComplaintDetails(int $complaintId): array;
    public function getComplaintTypes(): array;

    public function clearUserComplaintsCache(int $userId): void;
    public function clearComplaintDetailsCache(int $complaintId): void;
}
