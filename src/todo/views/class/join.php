<?php
$status = get_user_status();

if (!$status['logged_in']) {
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

// Get class passed in URL
$class_id = $class ?? null;
$q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
$q->execute([":id" => $class_id]);
$row = $q->fetch();
if ($row == null) {
    header('Location: ' . getRootPath() . 'todo/classes');
    exit;
}
$class_name = $row['name'];

// Update user requested_class if not already in class or if confined via form csrf token
if (!$status['is_in_class'] || is_csrf_valid()) {
    if ($status['is_in_class']) {
        leave_class($status['id'], $status['class_id']);
    }
    $q = getDB()->prepare("UPDATE users SET requested_class_id=:class_id WHERE id=:id");
    $q->execute([":class_id" => $class_id, ":id" => $status['id']]);
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}
if ($status['class_id'] == $class_id) {
    header('Location: ' . getRootPath() . 'todo/');
    exit;
}

$errors = array();

$title = "Rejoindre une classe | Todo list de classe";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/../inc/head.php' ?>
</head>
<body>
<?php include __DIR__ . '/../inc/header.php' ?>
<main class="">
    <section class="b-darken">
        <h3>Rejoindre la classe <?= out($class_name) ?></h3>

        <p>
            Vous êtes déjà dans une classe.<br>Êtes-vous sûr de vouloir la quitter pour rejoindre <?= out($class_name) ?>
        </p>
        <form action="" method="post">
            <?php set_csrf() ?>
            <input type="submit" value="Rejoindre <?= out($class_name) ?>">
            <?php print_errors_html($errors) ?>
        </form>
    </section>
</main>
<footer>
    <?= getFooter('<a href="' . getRootPath() . 'todo/classes">Liste des classes</a>', "Clément GRENNERAT") ?>
</footer>
</body>
<script src="<?= getRootPath() ?>todo/js/main.js"></script>
</html>