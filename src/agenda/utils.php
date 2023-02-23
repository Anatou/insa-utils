<?php
function randomToken($length)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($chars);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}

function randomCode($length)
{
    $chars = '0123456789';
    $input_length = strlen($chars);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $chars[mt_rand(0, $input_length - 1)];
    }
    return $random_string;
}

// $email does not include @insa-lyon.fr
function emailToName($email)
{
    return ucwords(strtolower(str_replace('.', ' ', $email)));
}

// $email does not include @insa-lyon.fr
function nameToEmail($name)
{
    return strtolower(str_replace(' ', '.', $name));
}

function timestampDiffMn($timestamp)
{
    if ($timestamp == null) {
        return strtotime("now");
    }
    return (strtotime("now") - strtotime($timestamp)) / 60;
}

function print_errors_html($errors)
{
    if (count($errors) > 0) {
        ?>
        <div class="error-container">
            <div class="errors">
                <?php
                foreach ($errors as $error) {
                    echo '<p>' . $error . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}
function print_infos_html($infos)
{
    if (count($infos) > 0) {
        ?>
        <div class="infos-container">
            <div class="infos">
                <?php
                foreach ($infos as $error) {
                    echo '<p>' . $error . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}

function set_cookie($name, $value): void
{
    setcookie($name, $value, [
        'expires' => time() + 60 * 60 * 24 * 365 * 5, // 5 years
        'path' => getRootPath() . 'agenda/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function remove_cookie($name): void
{
    setcookie($name, '', [
        'expires' => 0,
        'path' => getRootPath() . 'agenda/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}