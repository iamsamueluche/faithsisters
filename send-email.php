<?php

declare(strict_types=1);

$recipient = 'wearefaithsisters@gmail.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

function clean_input(string $value): string
{
    return trim(str_replace(["\r", "\n"], ' ', $value));
}

$firstName = clean_input($_POST['first_name'] ?? '');
$lastName = clean_input($_POST['last_name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone = clean_input($_POST['phone'] ?? '');
$subject = clean_input($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$honeypot = trim($_POST['website'] ?? '');

if ($honeypot !== '') {
    header('Location: contact.html?status=sent');
    exit;
}

if ($firstName === '' || $lastName === '' || $email === false || $subject === '' || $message === '') {
    header('Location: contact.html?status=invalid');
    exit;
}

$subjectLabels = [
    'general' => 'General Inquiry',
    'partnership' => 'Partnership',
    'volunteer' => 'Volunteering',
    'donation' => 'Donation',
    'other' => 'Other',
];

$subjectLabel = $subjectLabels[$subject] ?? 'Website Contact Form';
$fullName = trim($firstName . ' ' . $lastName);

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$host = strtolower(preg_replace('/:\d+$/', '', $host));
$host = preg_replace('/^www\./', '', $host);

if ($host === '' || $host === 'localhost') {
    $fromAddress = 'noreply@faithsisters.local';
} else {
    $fromAddress = 'noreply@' . $host;
}

$emailSubject = 'Faith Sisters Contact Form: ' . $subjectLabel;
$emailBody = implode("\n", [
    'A new contact form message was submitted from the Faith Sisters website.',
    '',
    'Name: ' . $fullName,
    'Email: ' . $email,
    'Phone: ' . ($phone !== '' ? $phone : 'Not provided'),
    'Subject: ' . $subjectLabel,
    '',
    'Message:',
    $message,
]);

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: Faith Sisters Website <' . $fromAddress . '>',
    'Reply-To: ' . $fullName . ' <' . $email . '>',
    'X-Mailer: PHP/' . phpversion(),
];

$sent = mail($recipient, $emailSubject, $emailBody, implode("\r\n", $headers));

header('Location: contact.html?status=' . ($sent ? 'sent' : 'error'));
exit;
