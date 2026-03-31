<?php

namespace App\Services\AI;

use App\Models\User;

/**
 * Interface cho phép swap LLM provider mà không sửa logic nghiệp vụ
 */
interface LlmServiceInterface
{
    public function processBookingRequest(string $userMessage, User $teacher, array $conversationHistory = []): array;
}
