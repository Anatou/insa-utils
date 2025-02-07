<?php
require '../template/head.php';
require '../template/header.php';
require '../template/footer.php';
require '../origin_path.php';
$name = "Cal'INSA";
$title = "Convertisseur de Calendrier ADE";
$desc = "Cette passerelle permet de convertir ton calendrier INSA en renommant les évènements pour les rendre plus lisibles. Entre le lien de ton calendrier ADE, puis ajoute sur ton hébergeur de calendrier préféré le lien d'abonnement iCal généré. À chaque actualisation, ce serveur convertira le calendrier ADE.";

$url = '';
if(isset($_GET['url'])) $url = urldecode($_GET['url']);
$mode = '';
if(isset($_GET['mode'])) $mode = urldecode($_GET['mode']);
$room = '';
if(isset($_GET['room'])) $room = urldecode($_GET['room']);

$year = date('Y');
if(date('m') < 9) $year--;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php printHead($name, $title, $desc, '', 'cal/icons/icon-128.png') ?>
    <link href="<?= getRootPath() ?>cal/main.css" rel="stylesheet"/>
</head>
<body>
<?php printHeader($name, $title) ?>
<main class="">
    <section class="b-darken">
        <p class="info">
            <?= $desc ?>
            <br>
            <br>
            Récupère le lien de ton calendrier ici&#8239;:
            <br>
            <a href="https://ade-outils.insa-lyon.fr/ADE-iCal@<?= $year ?>-<?= $year+1 ?>">https://ade-outils.insa-lyon.fr/ADE-iCal@<?= $year ?>-<?= $year+1 ?></a>.
        </p>

        <form action="./" method="get">
            <p>URL de ton calendrier ADE (lien abonnement iCal)&#8239;:</p>
            <input class="url-input" type="text" name="url" value="<?= $url ?>" placeholder="https://ade-outils.insa-lyon.fr/ADE-Cal:~jgarzer!<?= $year ?>-<?= $year+1 ?>:459877899af69B3D">
            <br><br>
            <p>Mode d'affichage des évènements&#8239;:</p>
            <select name="mode">
                <option value="0" <?= $mode == 0 ? 'selected' : '' ?>>Nom complet (CM Maths, TD Physique..)</option>
                <option value="1" <?= $mode == 1 ? 'selected' : '' ?>>Nom acronyme simplifié (CM MA, TD PH...)</option>
                <option value="2" <?= $mode == 2 ? 'selected' : '' ?>>Nom acronyme officiel (CM MA-AP, TD PH-AMP...)</option>
            </select>
            <br><br>
            <div>
                <input id="room-checkbox" type="checkbox" name="room" value="true" <?= $room ? 'checked' : '' ?>>
                <label for="room-checkbox">Afficher le nom de la salle dans le nom des évènements.</label>
            </div>
            <br>
            <input type="submit" value="Valider">
        </form>

        <?php
        if(isset($_GET['url'])){
            $url = 'https://insa-utils.fr/cal/get.php?url=' . $url . '&mode=' . $mode . '&room=' . $room;
            ?>
            <p>
                URL de ton calendrier convertis (nouvel abonnement iCal) :<br>
                <span style="word-break: break-all"><?php echo '<a href="' . $url . '">' . $url . '</a>'; ?></span>
            </p>
            <?php
        }
        ?>
    </section>
</main>
<footer>
    <?= getFooter("", "Clément GRENNERAT") ?>
</footer>
</body>
</html>
