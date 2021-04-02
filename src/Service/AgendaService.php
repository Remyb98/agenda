<?php


namespace App\Service;

use Exception;
use ICal\ICal;

class AgendaService
{
    private string $url;

    private string $uri;

    public const GROUPS = [
        "247",  // Classes
        "3345", // Groupe 1
        "3348", // Anglais D
        "3776", // MSH
    ];

    private const CLASSES = [
        "3A-AN3" => "Skill Consolidation",
        "3A-SH3C" => "Team Building",
        "3I-SI4" => "Introduction à l'IA",
        "3I-IN10" => "Base de données",
        "3I-IN11" => "Infographie 3D",
        "3I-IN12" => "Théorie des Graphes",
        "3I-SI2" => "Algèbre Linéaire",
        "3I-SI3" => "Traitement du Signal",

        "4A-AN2A" => "Anglais",
        "4A-SH1" => "Finance d'entreprise",
        "4A-SH2" => "Simulation de gestion d'entreprise",
        "4I-RV1" => "Projet encadré",
        "4I-RV2" => "Projet encadré",
        "4I-SI1" => "Algèbre avancé",
        "4I-SI2" => "Machine learning",
        "4I-SI3" => "Deep learning",
        "4I-IN1" => "Programmation C++",
        "4I-IN2" => "Système d'exploitation",
        "4I-IN3" => "Développement mobile",
        "4I-IN4" => "Réseaux",
        "4I-IN5" => "Génie logiciel",
        "4I-IN6" => "Développement web (front)",
        "4I-IN7" => "Enterprise Ressource Planning",
        "4I-IG1" => "Computer Graphics",
        "4I-IG2" => "Traitement et analyse d'images",
        "4I-IG3" => "Geometric Modeling",
        "4I-IG4" => "Unity",

        "5I-IN5" => "Réseaux"
    ];

    public function __construct()
    {
        $this->url = "https://planif.esiee.fr/jsp/custom/modules/plannings/anonymous_cal.jsp";
        $this->uri = "?resources={}&projectId=8&calType=ical&firstDate=2020-08-25&lastDate=2021-08-24";
    }

    public function getOriginalAgenda(string $groups): string
    {
        return file_get_contents($this->getLink($this->getGroups($groups)));
    }

    public function getParsedAgenda(string $groups): string
    {
        $calendar = new ICal($this->getLink($this->getGroups($groups)));
        $rawCalendar = $this->getRawAgenda($calendar->cal);
        return str_replace(["\r\n", "\r", "\n"], "\r\n", $rawCalendar);
    }

    public function getLink(array $groupsSelected): string
    {
        $groups = implode(",", $groupsSelected);
        $parsedUri = str_replace("{}", $groups, $this->uri);
        return $this->url . $parsedUri;
    }

    public function getRawAgenda(array $calendar): string
    {
        $parsedCalendar = "BEGIN:VCALENDAR\n";
        foreach ($calendar["VCALENDAR"] as $key => $value) {
            $parsedCalendar .= $key . ":" . $value . "\n";
        }
        if (key_exists("VEVENT", $calendar)) {
            $parsedCalendar .= $this->addEvents($calendar["VEVENT"]);
        }

        $parsedCalendar .= "END:VCALENDAR\n";
        return $parsedCalendar;
    }

    public function addEvents(array $events): string
    {
        $parsedEvents = "";
        foreach ($events as $event) {
            $parsedEvents .= "BEGIN:VEVENT\n";
            foreach ($event as $key => $value) {
                if (strpos($key, "array")) {
                    continue;
                }
                if ($key === "SUMMARY") {
                    $value = $this->changeEventSummary($value);
                }
                if ($key === "DESCRIPTION") {
                    $value = $this->formatDescription($value);
                }
                if ($key === "LOCATION") {
                    $value = $this->formatLocation($value);
                }
                $parsedEvents .= $key . ":" . $value . "\n";
            }
            $parsedEvents .= "END:VEVENT\n";
        }
        return $parsedEvents;
    }

    public function getGroups(string $groups): array
    {
        if ($groups === "all") {
            $groupsArray = self::GROUPS;
        } else {
            try {
                $groupsArray = explode(",", $groups);
            } catch (Exception $e) {
                $groupsArray = self::GROUPS;
            }
        }
        return $groupsArray;
    }

    public function changeEventSummary(string $eventName): string
    {
        $eventName = explode(":", $eventName);
        $eventName[0] = $this->formatEventName($eventName[0]);
        return $eventName[0] . " - " . $eventName[1];
    }

    public function formatEventName(string $name): string
    {
        $search = array_keys(self::CLASSES);
        $replace = array_values(self::CLASSES);
        return str_replace($search, $replace, $name);
    }

    public function formatDescription(string $description): string
    {
        $desc = preg_replace("/AURION\\\\n/", "", $description);
        $desc = preg_replace("/\d{10,}\\\\n/", "", $desc);
        return preg_replace("/\)\\\\n/", " from https://agenda.remybarberet.fr)", $desc);
    }

    public function formatLocation(string $location)
    {
        $loc = preg_replace("/\\\\,/", "\, ", $location);
        return preg_replace("/[V+]/", "", $loc);
    }
}
