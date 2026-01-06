<?php

namespace App\Repositories\Complaint;

use App\Repositories\Complaint\ComplaintRepositoryInterface;
use App\Models\Complaint;
use App\Models\ComplaintVersion;
use App\Models\ComplaintType;
use App\Models\ComplaintAttachment;
use Storage;


class ComplaintRepository implements ComplaintRepositoryInterface
{
    public function getUserComplaints(int $userId): array{
        $complaints = Complaint::with([
            'complaintType:id,type',
            'complaintDepartment:id,department_name',
            'complaintStatus:id,status'
        ])->where('user_id', $userId)->get();

        $latestVersions = ComplaintVersion::with('complaintStatus:id,status')
            ->whereIn('complaint_id', $complaints->pluck('id'))
            ->latest('id')
            ->get()
            ->groupBy('complaint_id');

        return $complaints->map(function ($complaint) use ($latestVersions) {
            $version = $latestVersions[$complaint->id][0] ?? null;

            return [
                'id' => $complaint->id,
                'complaint_type' => [
                    'id' => $complaint->complaintType->id,
                    'type' => $complaint->complaintType->type
                ],
                'complaint_department' => [
                    'id' => $complaint->complaintDepartment->id,
                    'department_name' => $complaint->complaintDepartment->department_name
                ],
                'location' => $complaint->location,
                'complaint_status' => [
                    'id' => $version->complaintStatus->id ?? $complaint->complaintStatus->id,
                    'status' => $version->complaintStatus->status ?? $complaint->complaintStatus->status
                ],
            ];
        })->values()->toArray();
    }

    public function getComplaintDetails(int $complaintId): array{
        $complaint = Complaint::with([
                        'complaintType:id,type',
                        'complaintDepartment:id,department_name',
                        'complaintStatus:id,status',
                    ])->findOrFail($complaintId);

        $complaintVersion = ComplaintVersion::with('complaintStatus:id,status')
                ->where('complaint_id', $complaintId)
                ->latest('id')
                ->first();

        $attachments = [] ;
            $attachmentsQuery = ComplaintAttachment::where('complaint_id', $complaintId);

            if ($complaintVersion) {
                $attachmentsQuery->where(function ($q) use ($complaintVersion) {
                    $q->whereNull('complaint_version_id')
                    ->orWhere('complaint_version_id', '<=', $complaintVersion->id);
                });
            }

            $attachments = $attachmentsQuery->get()->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'attachment' => url(Storage::url($attachment->attachment)),
                ];
            })->toArray();


            return [
                'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                'location' => $complaint['location'],
                'problem_description' => $complaintVersion->problem_description ?? $complaint['problem_description'],
                'complaint_status' => ['id' => $complaintVersion->complaintStatus->id ?? $complaint->complaintStatus['id'] , 'status' => $complaintVersion->complaintStatus->status ?? $complaint->complaintStatus['status']],
                'attachments' => $attachments
            ];
    }

    public function getComplaintTypes(): array{
        return ComplaintType::select('id', 'type')
            ->orderBy('type')
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'type' => $t->type])
            ->toArray();
    }

    public function clearUserComplaintsCache(int $userId): void {}
    
    public function clearComplaintDetailsCache(int $complaintId): void {}
}
