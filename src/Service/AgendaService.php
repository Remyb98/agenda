<?php


namespace App\Service;

use ICal\ICal;

class AgendaService
{
    private string $url;

    private string $uri;

    private const GROUPS = [
        "3172", // Groupe 1
        "3472", // Groupe Anglais
        "6467", // Groupe Management
    ];

    private const CLASSES = [
        "3I-IN10" => "Base de données",
        "3I-SI4" => "Introduction à l'IA",
        "3A-AN3" => "Skill Consolidation",
        "3A-SH3C" => "Team Building",
        "3I-IN11" => "Infographie 3D",
        "3I-IN12" => "Théorie des Graphes",
        "3I-SI2" => "Algèbre Linéaire",
        "3I-SI3" => "Traitement du Signal",
    ];

    public function __construct()
    {
        $this->url = "https://planif.esiee.fr/jsp/custom/modules/plannings/anonymous_cal.jsp";
        $this->uri = "?resources={}&projectId=6&calType=ical&nbWeeks=52";
    }

    public function getOriginalAgenda(): string
    {
        return file_get_contents($this->getLink());
    }

    public function getParsedAgenda(): string
    {
        $calendar = new ICal($this->getLink());
        $rawCalendar = $this->getRawAgenda($calendar->cal);
        return str_replace(["\r\n", "\r", "\n"], "\r\n", $rawCalendar);
    }

    public function getLink(): string
    {
        $groups = implode(",", self::GROUPS);
        $parsedUri = str_replace("{}", $groups, $this->uri);
        return $this->url . $parsedUri;
    }

    public function getRawAgenda(array $calendar): string
    {
        $parsedCalendar = "BEGIN:VCALENDAR\n";
        foreach ($calendar["VCALENDAR"] as $key => $value) {
            $parsedCalendar .= $key . ":" . $value . "\n";
        }
        foreach ($calendar["VEVENT"] as $event) {
            $parsedCalendar .= "BEGIN:VEVENT\n";
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
                $parsedCalendar .= $key . ":" . $value . "\n";
            }
            $parsedCalendar .= "END:VEVENT\n";
        }
        $parsedCalendar .= "END:VCALENDAR\n";
        return $parsedCalendar;
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
        return preg_replace("/\)\\\\n/", " from https://agenda.remybarberet.fr)\n", $desc);
    }
}
