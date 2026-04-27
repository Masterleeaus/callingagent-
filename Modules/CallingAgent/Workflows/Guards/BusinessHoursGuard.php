<?php
namespace Modules\CallingAgent\Workflows\Guards;

final class BusinessHoursGuard
{
    public function open(array $hours, ?int $timestamp = null): bool
    {
        $timestamp ??= time();
        $day = strtolower(date('D', $timestamp));
        $range = $hours[$day] ?? null;
        if (! $range) return false;
        $now = date('H:i', $timestamp);
        return $now >= ($range['open'] ?? '00:00') && $now <= ($range['close'] ?? '23:59');
    }
}
