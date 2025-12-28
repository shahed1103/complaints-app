<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use App\Models\Complaint;
use Exception;

class ComplaintTransactional
{
    public static function run(int $complaintId, callable $callback)
    {
        return DB::transaction(function () use ($complaintId, $callback) {

            $complaint = Complaint::where('id', $complaintId)
                ->lockForUpdate()
                ->first();

            if (!$complaint) {
                throw new Exception('Complaint not found', 404);
            }

            return $callback($complaint);
        });
    }
}
