<?php

namespace App\Services;

use App\Enums\DigitalServiceStatus;
use App\Models\DigitalServiceDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DigitalServiceService
{
    /**
     * @param  array{status: string, provider_reference?: ?string, token_number?: ?string, notes?: ?string}  $data
     */
    public function updateStatus(DigitalServiceDetail $detail, User $user, array $data): DigitalServiceDetail
    {
        $targetStatus = DigitalServiceStatus::tryFrom($data['status']);

        if ($targetStatus === null || $targetStatus === DigitalServiceStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Status tujuan harus Selesai atau Gagal.',
            ]);
        }

        if ($detail->status->isFinal()) {
            throw ValidationException::withMessages([
                'status' => 'Status layanan yang sudah final tidak dapat diubah lagi.',
            ]);
        }

        $oldStatus = $detail->status->value;

        return DB::transaction(function () use ($detail, $user, $data, $targetStatus, $oldStatus): DigitalServiceDetail {
            $detail->forceFill([
                'status' => $targetStatus,
                'provider_reference' => $this->cleanString($data['provider_reference'] ?? null) ?? $detail->provider_reference,
                'token_number' => $this->cleanString($data['token_number'] ?? null) ?? $detail->token_number,
                'notes' => $this->cleanString($data['notes'] ?? null) ?? $detail->notes,
                'completed_at' => $targetStatus === DigitalServiceStatus::Completed ? now() : null,
                'completed_by_id' => $targetStatus === DigitalServiceStatus::Completed ? $user->id : null,
            ])->save();

            Log::info('Status layanan digital diperbarui.', [
                'detail_id' => $detail->id,
                'old_status' => $oldStatus,
                'new_status' => $targetStatus->value,
                'actor_id' => $user->id,
            ]);

            return $detail->refresh();
        });
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
