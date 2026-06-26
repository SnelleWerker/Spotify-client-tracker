<?php

function daysInMonth(int $month, int $year): int
{
    return (int) date('t', mktime(0, 0, 0, $month, 1, $year));
}

function dateOnRenewalDay(int $renewalDay, int $month, int $year): DateTime
{
    $day = min($renewalDay, daysInMonth($month, $year));
    return new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day));
}

function getRenewalDate(array $user): DateTime
{
    $renewalDay = (int) $user['renewal_day'];
    $monthsPaid = max(1, (int) ($user['months_paid_in_advance'] ?? 1));
    $today = new DateTime('today');

    if (!empty($user['last_payment_date'])) {
        $due = new DateTime($user['last_payment_date']);
        $due->modify('+' . $monthsPaid . ' months');
        $y = (int) $due->format('Y');
        $m = (int) $due->format('m');
        return dateOnRenewalDay($renewalDay, $m, $y);
    }

    $y = (int) $today->format('Y');
    $m = (int) $today->format('m');
    $due = dateOnRenewalDay($renewalDay, $m, $y);

    if ($due < $today) {
        $m++;
        if ($m > 12) {
            $m = 1;
            $y++;
        }
        return dateOnRenewalDay($renewalDay, $m, $y);
    }

    return $due;
}

function getPaymentStatus(array $user): array
{
    $today = new DateTime('today');
    $renewal = getRenewalDate($user);
    $diff = (int) $today->diff($renewal)->format('%r%a');

    $monthsAdvance = (int) ($user['months_paid_in_advance'] ?? 1);
    $isPaid = (bool) $user['is_paid'];
    $advanceLabel = $monthsAdvance > 1 ? ' (PAID IN ADVANCE)' : '';

    if ($isPaid) {
        return [
            'status' => 'PAID' . $advanceLabel,
            'class' => 'status-paid',
            'message' => $monthsAdvance > 1
                ? 'Paid ' . $monthsAdvance . ' months in advance'
                : 'Paid for this month',
            'renewal_date' => $renewal->format('Y-m-d'),
            'days_diff' => $diff,
        ];
    }

    if ($diff < 0) {
        $days = abs($diff);
        return [
            'status' => 'OVERDUE',
            'class' => 'status-overdue',
            'message' => $days . ' day' . ($days !== 1 ? 's' : '') . ' overdue',
            'renewal_date' => $renewal->format('Y-m-d'),
            'days_diff' => $diff,
        ];
    }

    if ($diff <= 2) {
        return [
            'status' => 'DUE SOON',
            'class' => 'status-due-soon',
            'message' => $diff === 0 ? 'Due today' : 'Due in ' . $diff . ' day' . ($diff !== 1 ? 's' : ''),
            'renewal_date' => $renewal->format('Y-m-d'),
            'days_diff' => $diff,
        ];
    }

    return [
        'status' => 'UPCOMING',
        'class' => 'status-upcoming',
        'message' => 'Due in ' . $diff . ' days',
        'renewal_date' => $renewal->format('Y-m-d'),
        'days_diff' => $diff,
    ];
}

function getDashboardAlerts(array $users): array
{
    $alerts = [];
    foreach ($users as $user) {
        if ((bool) $user['is_paid']) {
            continue;
        }
        $info = getPaymentStatus($user);
        if ($info['days_diff'] < 0) {
            $days = abs($info['days_diff']);
            $alerts[] = [
                'type' => 'overdue',
                'text' => '❌ ' . $user['name'] . ' is ' . $days . ' day' . ($days !== 1 ? 's' : '') . ' overdue',
            ];
        } elseif ($info['days_diff'] <= 2) {
            $alerts[] = [
                'type' => 'due-soon',
                'text' => '⚠ ' . $user['name'] . ' is due in ' . $info['days_diff'] . ' day' . ($info['days_diff'] !== 1 ? 's' : ''),
            ];
        }
    }
    return $alerts;
}
