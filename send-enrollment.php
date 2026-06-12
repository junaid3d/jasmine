<?php
$recipient = 'info@jasminelearning.com';
$subject = 'New Jasmine Learning Enrollment';
$phone = '0308-4734673';
$site_email = 'info@jasminelearning.com';

function clean_text($value) {
    $value = trim((string) $value);
    $value = str_replace(array("\r", "\n", "\0"), '', $value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function clean_email($value) {
    $value = trim((string) $value);
    $value = str_replace(array("\r", "\n", "\0"), '', $value);
    return filter_var($value, FILTER_SANITIZE_EMAIL);
}

function normalize_phone($value) {
    $value = preg_replace('/[\s\-\(\)\.]/', '', (string) $value);

    if (preg_match('/^\+923\d{9}$/', $value) === 1) {
        return '0' . substr($value, 3);
    }

    return $value;
}

function is_valid_local_mobile($value) {
    return preg_match('/^03\d{9}$/', normalize_phone($value)) === 1;
}

function normalize_spam_text($value) {
    $value = strtolower((string) $value);
    $value = strtr($value, array(
        '0' => 'o',
        '3' => 'e',
        '$' => 's',
        '5' => 's',
        '!' => 'i',
        '1' => 'i',
        '|' => 'i',
        '@' => 'a'
    ));
    return preg_replace('/[^a-z0-9]+/', '', $value);
}

function has_spam_content($value) {
    $raw = strtolower((string) $value);
    $compact = normalize_spam_text($value);
    $url_pattern = '/(https?:\/\/|www\.|[a-z0-9-]+\.(com|net|org|io|co|live|site|info|biz)\b)/i';
    $phrase_pattern = '/(price\s*list|action\s*plan|complete\s*seo|place\s+your\s+website|reviewing\s+your\s+website|qualified\s+traffic|online\s+search\s+results)/i';
    $compact_patterns = array(
        'seo',
        'searchengineoptimization',
        'googlefirstpage',
        'googleistpage',
        'firstpage',
        'istpage',
        'rankyourwebsite',
        'ranking',
        'searchvisibility',
        'organictraffic',
        'websitetraffic',
        'backlinks',
        'domainauthority',
        'seopackages',
        'seoproposal',
        'seopricing',
        'searchindex',
        'googlesearchindex',
        'searchresults'
    );

    if (preg_match($url_pattern, $raw) === 1 || preg_match($phrase_pattern, $raw) === 1) {
        return true;
    }

    foreach ($compact_patterns as $pattern) {
        if (strpos($compact, $pattern) !== false) {
            return true;
        }
    }

    return false;
}

function render_page($title, $message, $is_success = true) {
    $accent = $is_success ? '#73805f' : '#a05d4b';
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' | Jasmine Learning</title>';
    echo '<style>';
    echo 'body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f7f1e6;color:#2f3a27;font-family:Arial,sans-serif;line-height:1.6;padding:24px}';
    echo '.card{max-width:680px;background:#fffdf7;border:1px solid rgba(47,58,39,.16);border-radius:28px;padding:42px;box-shadow:0 24px 70px rgba(47,58,39,.12)}';
    echo 'h1{margin:0 0 16px;font-family:Georgia,serif;font-size:clamp(2.2rem,6vw,4rem);line-height:1.05;color:#2f3a27}';
    echo 'p{margin:0 0 24px;color:#59664c;font-size:1.06rem}.mark{width:52px;height:6px;background:' . $accent . ';border-radius:999px;margin-bottom:24px}';
    echo 'a{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 20px;border-radius:999px;background:#2f3a27;color:#fffdf7;text-decoration:none;font-weight:700}';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<main class="card">';
    echo '<div class="mark" aria-hidden="true"></div>';
    echo '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="index.html">Return to Jasmine Learning</a>';
    echo '</main>';
    echo '</body>';
    echo '</html>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_page('Enrollment Form', 'Please use the enrollment form on the Jasmine Learning page.', false);
    exit;
}

$parent_name = clean_text(isset($_POST['parent_name']) ? $_POST['parent_name'] : '');
$child_name = clean_text(isset($_POST['child_name']) ? $_POST['child_name'] : '');
$child_age = clean_text(isset($_POST['child_age']) ? $_POST['child_age'] : '');
$phone_number = clean_text(isset($_POST['phone']) ? $_POST['phone'] : '');
$email = clean_email(isset($_POST['email']) ? $_POST['email'] : '');
$constructivist = clean_text(isset($_POST['constructivist_familiarity']) ? $_POST['constructivist_familiarity'] : '');
$expectations = clean_text(isset($_POST['expectations']) ? $_POST['expectations'] : '');
$medical_notes = clean_text(isset($_POST['medical_notes']) ? $_POST['medical_notes'] : '');
$support_needs = clean_text(isset($_POST['support_needs']) ? $_POST['support_needs'] : '');
$referral_name = clean_text(isset($_POST['referral_name']) ? $_POST['referral_name'] : '');
$referral_contact = clean_text(isset($_POST['referral_contact']) ? $_POST['referral_contact'] : '');
$community_disclaimer = isset($_POST['community_disclaimer']) ? clean_text($_POST['community_disclaimer']) : '';

if ($parent_name === '' || $child_name === '' || $child_age === '' || $phone_number === '' || $email === '' || $expectations === '' || $medical_notes === '' || $support_needs === '' || $referral_name === '' || $referral_contact === '' || $community_disclaimer !== 'accepted') {
    render_page('Missing Details', 'Please complete the required enrollment details and try again.', false);
    exit;
}

if (!is_valid_local_mobile($phone_number)) {
    render_page('Invalid number', 'Invalid number', false);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_page('Invalid Email', 'Please enter a valid email address and submit the form again.', false);
    exit;
}

$spam_check_values = array($parent_name, $child_name, $expectations, $medical_notes, $support_needs, $referral_name, $referral_contact);
foreach ($spam_check_values as $spam_check_value) {
    if (has_spam_content(htmlspecialchars_decode($spam_check_value, ENT_QUOTES))) {
        render_page('Thank you.', 'Your enrollment request has been received. Bank transfer details will be shared with you shortly.', true);
        exit;
    }
}

$allowed_familiarity = array('Yes', 'No', 'A little');
if (!in_array(htmlspecialchars_decode($constructivist, ENT_QUOTES), $allowed_familiarity, true)) {
    $constructivist = 'Not provided';
}

$body_lines = array(
    'New Jasmine Learning Enrollment',
    '',
    'Parent/Guardian Name: ' . htmlspecialchars_decode($parent_name, ENT_QUOTES),
    'Child Name: ' . htmlspecialchars_decode($child_name, ENT_QUOTES),
    'Child Age: ' . htmlspecialchars_decode($child_age, ENT_QUOTES),
    'Phone Number: ' . htmlspecialchars_decode($phone_number, ENT_QUOTES),
    'Email Address: ' . $email,
    'Familiar with constructivist education: ' . htmlspecialchars_decode($constructivist, ENT_QUOTES),
    '',
    'Expectations:',
    htmlspecialchars_decode($expectations, ENT_QUOTES),
    '',
    'Allergies, medical conditions, or sensitivities:',
    htmlspecialchars_decode($medical_notes, ENT_QUOTES),
    '',
    'Emotional, behavioral, learning, or social needs:',
    htmlspecialchars_decode($support_needs, ENT_QUOTES),
    '',
    'Referral Name: ' . htmlspecialchars_decode($referral_name, ENT_QUOTES),
    'Referral Contact #: ' . htmlspecialchars_decode($referral_contact, ENT_QUOTES),
    'Community Disclaimer: Accepted',
    '',
    'Submitted from jasminelearning.com'
);

$body = implode("\n", $body_lines);

$reply_name = htmlspecialchars_decode($parent_name, ENT_QUOTES);
$reply_name = preg_replace('/[^A-Za-z0-9 ._\'-]/', '', $reply_name);

$headers = array(
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: Jasmine Learning Website <' . $site_email . '>',
    'Reply-To: ' . $reply_name . ' <' . $email . '>',
    'Return-Path: ' . $site_email,
    'X-Mailer: PHP/' . phpversion()
);

$sent = mail($recipient, $subject, $body, implode("\r\n", $headers), '-f' . $site_email);

if ($sent) {
    render_page('Thank you.', 'Your enrollment request has been received. Bank transfer details will be shared with you shortly.', true);
} else {
    render_page('We could not send your request.', 'Sorry, your enrollment request could not be sent right now. Please contact ' . $phone . ' and we will help you directly.', false);
}
?>
