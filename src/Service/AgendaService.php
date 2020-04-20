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
        "3I-SI4" => "Python Scientifique",
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

    public function getParsedCalendar(): string
    {
        $calendar = new ICal($this->getLink());
        return $this->getRawCalendar($calendar->cal);
    }

    private function getLink(): string
    {
        $groups = implode(",", self::GROUPS);
        $parsedUri = str_replace("{}", $groups, $this->uri);
        return $this->url . $parsedUri;
    }

    private function getRawCalendar(array $calendar): string
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
                    $value = $this->changeEventDescription($value);
                }
                $parsedCalendar .= $key . ":" . $value . "\n";
            }
            $parsedCalendar .= "END:VEVENT\n";
        }
        $parsedCalendar .= "END:VCALENDAR\n";
        return $parsedCalendar;
    }

    private function changeEventDescription(string $eventName): string
    {
        $eventName = explode(":", $eventName);
        $eventName[0] = $this->formatEventName($eventName[0]);
        return $eventName[0] . " - " . $eventName[1];
    }

    private function formatEventName(string $name): string
    {
        $search = array_keys(self::CLASSES);
        $replace = array_values(self::CLASSES);
        return str_replace($search, $replace, $name);
    }
}
