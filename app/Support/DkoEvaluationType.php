<?php

namespace App\Support;

final class DkoEvaluationType
{
    /**
     * KA-VR-afspraak: instrument wordt individueel beoordeeld (ICJ),
     * alle overige vakken klassikaal (KCJ). Vakbenamingen in imports
     * worden gecontroleerd; niet automatisch "instrumentaal" als Instrument beschouwen.
     */
    public static function forSubjectName(string $name): string
    {
        return preg_match('/^\\s*instrument(?:\\b|[\\s(:\\-])/iu', $name) ? 'icj' : 'kcj';
    }
}
