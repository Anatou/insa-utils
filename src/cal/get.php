<?php
require "../../vendor/autoload.php";

use ICal\ICal;

function strstr_after($string, $needle): string
{
    $result = substr(strstr($string, $needle), strlen($needle));
    if ($result) {
        return $result;
    }
    return $string; // Return $string if needle not found
}

function strstr_before($string, $needle): string
{
    $result = strstr($string, $needle, true);
    if ($result) {
        return $result;
    }
    return $string; // Return $string if needle not found
}

function strstr_between($string, $needle1, $needle2): string
{
    return strstr_after(strstr_before($string, $needle2), $needle1);
}

function get_after_last_occurrence_of($string, $needle): string
{
    return substr($string, strrpos($needle . $string, $needle));
}

function convertCalendar($url, $mode, $room): void
{
    try {
        $ical = new ICal("ICal.ics", [
            "defaultSpan" => 2, // Default value
            "defaultTimeZone" => "UTC",
            "defaultWeekStart" => "MO", // Default value
            "disableCharacterReplacement" => false, // Default value
            "filterDaysAfter" => null, // Default value
            "filterDaysBefore" => null, // Default value
            "httpUserAgent" => null, // Default value
            "skipRecurrence" => false, // Default value
        ]);
        $ical->initUrl(
            $url,
            $username = null,
            $password = null,
            $userAgent = null
        );

        header("Content-type:text/calendar");
        header("Content-Disposition:attachment;filename=edt_insa.ics");

        echo "BEGIN:VCALENDAR\r\n";
        echo "METHOD:REQUEST\r\n";
        echo "PRODID:-//themsVPS/version 1.0\r\n";
        echo "VERSION:2.0\r\n";
        echo "CALSCALE:GREGORIAN\r\n";

        foreach ($ical->events() as $i => $event) {
            editEventAndPrint($event, $mode, $room);
        }

        echo "END:VCALENDAR\r\n";
    } catch (\Exception $e) {
        die($e);
    }
}

function editEventAndPrint($event, $mode, $room)
{
    $subject = strstr_between($event->description, "] ", "\n("); // Full name
    $classDetails = strstr_between($event->description, "\n(", ")\n");

    $firstExplodedSummary = explode("::", $event->summary); // Exploding  FIMI:2:S1::MA-TF:TD::048 #011 into [FIMI:2:S1, MA-TF:TD, 048]
    $explodedSummary = [];
    $explodedFormationDetails = [];

    if (count($firstExplodedSummary) >= 2) {
        $explodedSummary = explode(":", $firstExplodedSummary[1]); // [MA-TF, TD]
        $explodedFormationDetails = explode(":", $firstExplodedSummary[0]); // [FIMI, 2, S1]
    }

    $formation = count count($explodedFormationDetails) >= 2 ? $explodedFormationDetails[0] : ""; // FIMI, IF, GI
    $subjectTag = count($explodedSummary) >= 1 ? $explodedSummary[0] : ""; // MA-TF
    $type = count($explodedSummary) >= 2 ? $explodedSummary[1] : null; // CM, TD, TP, EV => IE, EDT => Autre

    if (
        $subjectTag === "SOU" ||
        $subjectTag === "LV" ||
        ($subjectTag == "EPS" && $type == "EDT") ||
        ($subjectTag == "*" && $type == "EDT" && $classDetails == "Créneau P2i")
    ) {
        // Matières à ne pas afficher : Créneaux Soutien, Langues et Sport
        return;
    }
    $p2i_number = "";
    if (str_starts_with($subjectTag, "P2I")) {
        $p2i_number = substr($subjectTag, 3, 1);
        $subjectTag = "P2I"; // P2I2-TF-SH2 => P2I
    }

    if ($type == "EDT") {
        $type = null;
    }

    // Location

    if ($event->location == null) {
        if (str_contains($classDetails, "Amphi Capelle")) {
            $location = "Amphi Capelle";
            $fullLocation = "Amphi Capelle";
        } else {
            $location = null;
            $fullLocation = null;
        }
    } else {
        $location = join(
            ", ",
            array_map(function ($loc) {
                $room = strstr_before($loc, " (");
                $room = get_after_last_occurrence_of($room, " - ");

                if (str_starts_with($room, "Amphithéâtre")) {
                    $amphiExploded = explode(" ", $room);
                    if (count($amphiExploded) >= 3) {
                        return "Amphi " .
                            $amphiExploded[count($amphiExploded) - 1]; // Takes only the last word : William Hamilton => Hamilton
                    }
                    array_shift($amphiExploded);
                    return "Amphi " . join(" ", $amphiExploded);
                }
                return $room;
            }, explode(",", $event->location))
        );

        $fullLocation = join(
            " / ",
            array_map(function ($loc) {
                $fullRoom = strstr_after($loc, " - ");
                return str_replace("Amphithéâtre", "Amphi", $fullRoom);
            }, explode(",", $event->location))
        );
    }

    // Modes : 0 = full name, 1 = short, 2 = default
    if ($mode != 2) {
        $subjectTag = explode("-", $subjectTag)[0]; // explodes from MA-TF to [MA, TF]
        if ($mode != 1) {
            // Default, Human full readable names
            if ($formation == "FIMI") {
                $subjectTag = match ($subjectTag) {
                    "PH" => "Physique",
                    "MA" => "Maths",
                    "CO", "CP" => "Conception",
                    "CH" => "Chimie",
                    "TH" => "Thermo",
                    "MS" => "Méca",
                    "ANG" => "Anglais",
                    "EPS" => "Sport",
                    "P2I" => "P2I" . $p2i_number . " - " . $classDetails,
                    "*" => $subject,
                    default => $subjectTag,
                };
            } else if ($formation == "IF") {
                $subjectTag = match ($subjectTag) {
                    "AC" => "Architecture des circuits numériques",
                    "AO" => "Architecture des ordinateurs",
                    "PRC" => "Programmation en C",
                    "ALGO" => "Algorithmes et structures de données",
                    "POO1" => "Programmation Orientée Objet - C++ - Les Bases",
                    "POO2" => "Programmation Orientée Objet - C++ - Avancée",
                    "OP" => "Outils de Programmation",
                    "CMSI" => "Calcul matriciel et synthèse d'images",
                    "TSI" => "Traitement du Signal et des Images",
                    "MD" => "Modélisation des données",
                    "MP" => "Modélisation des Processus",
                    "BDR" => "Système de gestion de base de données",
                    "SHC1" => "Sciences Humaines et Communication",
                    "PPP1" => "Projet Personnel et Professionnel",
                    "LV1" => "Langue Vivante 1",
                    "LV2" => "Langue Vivante 2",
                    "EPS" => "Éducation Physique et Sportive",
                    "*" => $subject,
                    default => $subjectTag,
                };
            } else if ($formation == "GI") {
                $subjectTag = match ($subjectTag) {
                    "GIN" => "Gestion industrielle",
                    "APS" => "Systèmes automatisés de production",
                    "APM" => "Algorithmique, programmation et modélisation en UML",
                    "ROO" => "Recherche Opérationnelle",
                    "PSX" => "Probabilités, statistiques, plans d'expériences",
                    "RDM" => "Résistance Des Matériaux",
                    "PFI" => "Procédés de fabrication, industrialisation",
                    "PSC" => "Penser système et cycle de vie",
                    "COM" => "Théâtre Sciences humaines et Communication",
                    "PPP" => "Projet Personnel Professionnel",
                    "LV1" => "Langue Vivante 1",
                    "LV2" => "Langue Vivante 2",
                    "EPS" => "Éducation Physique et Sportive",
                    "*" => $subject,
                    default => $subjectTag,
                };
            } else if ($formation == "HU") {
                // matières des humas
                $subjectTag = match ($subjectTag) {
                    "ALL" => "Allemand",
                    "ANG" => "Anglais",
                    "ARA" => "Arabe",
                    "CHI" => "Chinois",
                    "ESP" => "Espagnol",
                    "FLE" => "Français Langue Étrangère",
                    "ITA" => "Italien",
                    "JAP" => "Japonais",
                    "POR" => "Portugais",
                    "RUS" => "Russe",
                    "TAN" => "Tandem",
                    "*" => $subject,
                    default => $subjectTag,
                };

        } elseif ($subjectTag == "P2I") {
            $subjectTag = "P2I" . $p2i_number . " - " . $classDetails;
        }
    }

    $locationInSummary = $room && $room != "false";

    $event->summary =
        ($type == null ? "" : $type . " ") .
        $subjectTag .
        ($location == null || !$locationInSummary ? "" : " - " . $location);
    $event->location = $fullLocation;

    printEvent($event);
}

function printEvent($event): void
{
    echo "BEGIN:VEVENT\r\n";
    echo getEventDataString($event);
    echo "END:VEVENT\r\n";
}

function getEventDataString($event): string
{
    $data = [
        "SUMMARY" => $event->summary,
        "DTSTART" => $event->dtstart,
        "DTEND" => $event->dtend,
        /*'DTSTART_TZ' => $event->dtstart_tz,
         'DTEND_TZ' => $event->dtend_tz,*/
        "DURATION" => $event->duration,
        "DTSTAMP" => $event->dtstamp,
        "UID" => $event->uid,
        "CREATED" => $event->created,
        "LAST-MODIFIED" => $event->last_modified,
        "DESCRIPTION" => $event->description,
        "LOCATION" => $event->location,
        "SEQUENCE" => $event->sequence,
        "STATUS" => $event->status,
        "TRANSP" => $event->transp,
        "ORGANISER" => $event->organizer,
        "ATTENDEE(S)" => $event->attendee,
    ];

    // Remove any blank values
    $data = array_filter($data);

    $output = "";
    foreach ($data as $key => $value) {
        $output .= sprintf("%s:%s\r\n", $key, str_replace("\n", "\\n", $value));
    }
    return $output;
}

if (isset($_GET["url"])) {
    convertCalendar(urldecode($_GET["url"]), $_GET["mode"], $_GET["room"]);
} else {
    header("Location: ./");
}
