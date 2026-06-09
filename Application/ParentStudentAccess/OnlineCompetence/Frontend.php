<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineCompetence;

use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendOnlineCompetence(): Stage
    {
        $stage = new Stage('Kompetenzübersicht');

        // todo Person auswählen

        // todo Fach auswählen
//        Debugger::devDump(OnlineCompetence::useService()->getPersonListAndSourceFromAccountBySession());

        // todo Schuljahr? eventuell wie bei Bewertung -> kann alte Schuljahre einblenden lassen

        // todo Quellcode von Schüleransicht direct verwenden mit Parametern
        // todo (Bild anzeigen, kann im Verlauf Kompetenzen bewerten, anzeigen ab Datum, nur bewertet Kompetenzen anzeigen)
        // todo extra api für diesen Teil die dann auch Lehrer und Eltern/Schüler bekommen

        return $stage;
    }
}