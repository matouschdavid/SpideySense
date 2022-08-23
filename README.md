# Einführung in dieses Php-Framework

## Components

Unter /views findest du die index.html. In dieser File findest du den größten Unterschied zu der Entwicklungsweise, die du bisher kennengelernt hast.
Eine Seite besteht nicht mehr aus *einer* html File, *einer* css File und *einer* js File, sondern aus mehreren Komponenten, die jeweils aus diesen drei Dateien bestehen.
Diese Komponenten sollen dir helfen deinen html Code zu struktieren und große Dateien vermeiden.

Eine spezielle Komponente ist die \<routing-component />. Diese ist ein Platzhalter für die jeweilige Seite, die der Nutzer aufruft.

Einbinden kannst du Komponenten mit dem Namen, sofern die Komponenten in /views direkt liegen. Wenn sie in einem Unterordner liegen musst du die Komponente in der **routingConfiguration.config** File registrieren.

Eine neue Komponente anlegen kannst du mit dem generate.bat File

```cmd

generate.bat nameOfTheComponent
```

## Controllers

Unter /Presentation/Controllers findest du einen Homecontroller. Ein Controller regelt welche Seite in welcher Situation angezeigt werden soll.
Der HomeController kann um Methoden erweitert werden, die aber alle mit der Homepage zu tun haben müssen. Wenn du die Restaurants einer Region anzeigen willst, regelt dies z.B. ein RestaurantController.

Die Methoden können nicht willkürlich benannt werden. Sie müssen dem folgenden Schema folgen: REST-Methode(GET|POST|PUT|DELETE)_\<Action>

Die Action ist ein Name den du selbst festlegen kannst. Wenn du ein Formular hast mit dem der User einen neuen Nutzer anlegt, wäre ein passender Name für die Methode *POST_SignUp()*.

In dieser Methode sollst du Queries und Commands aufrufen. Ein Query gibt Daten zurück und ein Command führt eine Aktion aus.
Die Restaurants einer Region zu erhalten, ist ein Query.
Ein Restaurant bewerten ein Command.

Zurück geben sollen deine Methoden im Controller eine View. Dies kannst du mit:

```php
// Name der Komponente die angezeigt werden soll
return $this->view('home');

// oder

//1. Param ist der Name des Controllers
//2. Param ist die Action
//Redirect ist immer ein GET und ruft hier die Methode im HomeController GET_index auf
return $this->redirect('home', 'index');
```

Eine GET-Action soll immer \$this->view() aufrufen und eine POST-Action immer $this->redirect().

## index.php

Dieses Framework verwendet Dependency Injection. Das ermöglicht dir, dass du in jedem Controller, Query, Command oder Repository andere Klassen verwenden kannst, ohne die Objekte dafür zu selbst erstellen.

```php

class Home extends Controller{

    public function __construct(
        //Der Home Controller will ein TitleQuery Objekt bekommen
        //und durch Dependency Injection bekommt er dieses auch.
        //Du musst dir keine gedanken machen, woher es kommt und wie es
        //erstellt wird.
        private \Application\TitleQuery $titleQuery
    ) {
    }
}
```

Das kannst du mit allen Klassen machen, die du vorher in der index.php registriert hast.

```php

$sp->register(\Application\TitleQuery::class);
```

Wenn der Konstruktor dieser Klasse entweder keine Parameter oder nur Parameter nimmt, die ebenfalls schon registriert sind, geht das.
Wenn dein Kontruktor jedoch z.B. einen string nimmt, kannst du eine Lambda-Funktion angeben, die angibt, wie dieses Objekt erstellt werden soll, jedes Mal, wenn es jemand braucht.

```php

$sp->register(\Application\TitleQuery::class, function(){
    return new \Application\TitleQuery("importantString");
});
```

## Views

Die html Datei einer Komponente ist keine normale html Datei. In dieser kannst du php einbinden, als wäre es eine Php File.

Es gibt jedoch zusätzlich noch ein paar QualityOfLife Verbesserungen.
Es gibt nur mehr Php Öffnungstags. Geschlossen werden diese automatisch, wenn html Code erkannt wird. Das Zeichen zum Öffnen ist @ und nicht <?php.

Bei der $this->view() Methode kannst du zusätzlich zur Seite die aufgerufen wird auch noch ein data Array übergeben. Die Werte aus diesem Array kannst du in deinem html File einbinden.

```php
<p>{{ 3 * 4 }}</p> //{{}} ist ein echo. Gibt also 12 aus
<p>{{ [{nr1}] * [{nr2}] }}</p> // [{}] gibt den Wert einer Variable des data Arrays zurück
<h1>[[title]]</h1> //Gibt den title aus dem data Array aus

```

Ifs und For-Schleifen kannst du auch ganz leicht einbinden mit:

```php

// verwendet shouldDisplay aus data Array
<h1 myIf="[{shouldDisplay}]">Sometimes hidden</h1>

<div myFor="[{restaurants}] as $restaurant">
    // Du musst einer Komponente immer ein Data Array mitgeben!
    // Wenn du das gesamte data Array übergeben willst: data="$data"
    <restaurant-card-component data="['restaurant' => $restaurant]"/>
</div endFor>
```

**Am Ende das endIf und endFor nicht vergessen!**