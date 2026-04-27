<?php
namespace Modules\CallingAgent\AI\Tools;

final class LeadExtractorTool
{
    public function extract(string $text): array
    {
        preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $email);
        preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $phone);
        return ['email' => $email[0] ?? null, 'phone' => $phone[0] ?? null, 'raw' => $text];
    }
}
