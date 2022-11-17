<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Extension;

abstract class FrontendBasic extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";
}