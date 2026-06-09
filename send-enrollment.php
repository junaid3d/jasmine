<?php
$recipient = 'info@jasminelearning.com';
$subject = 'New Jasmine Learning Enrollment';
$phone = '0308-4734673';

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

if ($parent_name === '' || $child_name === '' || $child_age === '' || $phone_number === '' || $email === '' || $expectations === '') {
    render_page('Missing Details', 'Please complete the required enrollment details and try again.', false);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_page('Invalid Email', 'Please enter a valid email address and submit the form again.', false);
    exit;
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
    'Submitted from jasminelearning.com'
);

$body = implode("\n", $body_lines);

$headers = array(
    'From: Jasmine Learning Website <' . $recipient . '>',
    'Reply-To: ' . htmlspecialchars_decode($parent_name, ENT_QUOTES) . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8'
);

$sent = mail($recipient, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    render_page('Thank you.', 'Your enrollment request has been received. Bank transfer details will be shared with you shortly.', true);
} else {
    render_page('We could not send your request.', 'Sorry, your enrollment request could not be sent right now. Please contact ' . $phone . ' and we will help you directly.', false);
}
?>
