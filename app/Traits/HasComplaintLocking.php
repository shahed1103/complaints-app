<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Auth;

trait HasComplaintLocking
{

    public function isLocked(): bool{
        if (!$this->locked_at) {
            return false;
        }

        $expires = $this->locked_at->addMinutes($this->lock_timeout);

        return now()->lessThan($expires);
    }

    public function lock($employeeId){
        if ($this->isLocked() && $this->locked_by != $employeeId) {
            throw new Exception("This complaint is currently locked by another employee.", 409);
        }

        if (!$this->isLocked()) {
            $this->locked_by = $employeeId;
            $this->locked_at = now();
            $this->save();
        }
    }

    public function unlock(){
        $this->locked_by = null;
        $this->locked_at = null;
        $this->save();
    }
}
