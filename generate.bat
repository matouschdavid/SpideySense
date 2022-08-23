@echo off

if %1 == component (
mkdir .\views\%2-component

echo ^<p^>%2 works^</p^> > ./views/%2-component/.html
echo /* TODO  */ > ./views/%2-component/.css
echo /* TODO  */ > ./views/%2-component/.js
) else if %1 == controller (
    echo. ^<?php > ./src/Presentation/Controllers/%2.php
    echo. namespace Presentation\Controllers; >> ./src/Presentation/Controllers/%2.php
    echo. use Presentation\MVC\ActionResult; >> ./src/Presentation/Controllers/%2.php
    echo. use Presentation\MVC\Controller; >> ./src/Presentation/Controllers/%2.php
    echo. class %2 extends Controller{  >> ./src/Presentation/Controllers/%2.php
    echo. public function __construct^(^){}  >> ./src/Presentation/Controllers/%2.php
    echo. public function GET_Index^(^) : ActionResult{ >> ./src/Presentation/Controllers/%2.php
    echo. return $this-^>view^('Insert your component here'^); >> ./src/Presentation/Controllers/%2.php
    echo. } >> ./src/Presentation/Controllers/%2.php
    echo. } >> ./src/Presentation/Controllers/%2.php
)