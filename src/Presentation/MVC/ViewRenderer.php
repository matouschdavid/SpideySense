<?php

namespace Presentation\MVC;

final class ViewRenderer
{
    private function __construct()
    {
    }

    public static function parsePhpEndings(string $content): string
    {
        $isInPhpMode = false;
        $alreadyScannedContent = '';
        $len = strlen($content);
        for($i = 0; $i < $len; $i++){
            $currentChar = $content[$i];
            if($isInPhpMode && (($i < $len-4 && $content[$i+1] == '?' && $content[$i+2] == 'p') || ($currentChar == '@'))){
                if($currentChar != '@'){
                    $i += 4;
                }
                continue;
            }
            if($isInPhpMode){
                if($currentChar == '<'){
                    $isInPhpMode = false;
                    $alreadyScannedContent .= '*}';
                }else if($i < $len-2 && $currentChar == '*' && $content[$i+1] == '}'){
                    $isInPhpMode = false;
                }
            }else{
                if($currentChar == '@'){
                    $isInPhpMode = true;
                }
            }

            $alreadyScannedContent .= $currentChar;
        }
        if($isInPhpMode){
            $alreadyScannedContent .= '*}';
        }
        return $alreadyScannedContent;
    }

    public static function findPatterns(string $pattern, string $content){
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        $contentExtensions = array(array());
        $matches = $matches[0];
            if(sizeof($matches) > 0){
                foreach ($matches as $match) {
                    $text = $match[0];
                    $pos = $match[1];
                    $startTagPos = $pos;
                    for ($i=$pos; $i >= 0; $i--) {
                        if($content[$i] == '<'){
                            $startTagPos = $i;
                            break;
                        }
                    }
                    $contentExtensions[0] += [$startTagPos => $text];
                }
            }
        return $contentExtensions[0];
    }

    public static function parseForsAndIfs(string $content): string{
        $contentExtensions = self::findPatterns('/(myFor=\".*\")|(myIf=\".*\")|(myElseIf=\".*\")|(myElse)/', $content);
        $newContent = "";
        for ($i=0; $i < strlen($content); $i++) { 
            if(array_key_exists($i, $contentExtensions)){
                $value = $contentExtensions[$i];
                if(str_starts_with($value, 'myFor')){
                    $forContent = explode('"', $value)[1];
                    $newContent .= '@foreach (' . $forContent . ') :';
                }else if(str_starts_with($value, 'myElseIf')){
                    $ifContent = explode('"', $value)[1];
                    $newContent .= '@}elseif (' . $ifContent . '){';
                }else if(str_starts_with($value, 'myElse')){
                    $newContent .= '@}else{';
                }else if(str_starts_with($value, 'myIf')){
                    $ifContent = explode('"', $value)[1];
                    $newContent .= '@if (' . $ifContent . '){';
                }
            }
            $newContent .= $content[$i];
        }
        $content = $newContent;
        $patterns = [
            '(myFor=".*")',
            '(endFor>)',
            '(myIf=".*")',
            '(endIf>)',
            '(myElseIf=".*")',
            '(myElse)'
        ];
        $replacements = [
            '',
            '> @endforeach;',
            '',
            '> @}',
            '',
            ''
        ];
        $content = preg_replace($patterns, $replacements, $content);
        return $content;
    }

    public static function getComponentDefinitions(string $viewPath): array{
        $routingConfig = file("$viewPath/routingConfiguration.config", FILE_SKIP_EMPTY_LINES);
        $components = array();
        foreach ($routingConfig as $config) {
            $parts = explode(" => ", $config);
            $components[$parts[0]] = $parts[1];
        }
        return $components;
    }

    public static function parseComponents(string $content, string $viewPath): string{
        $components = self::getComponentDefinitions($viewPath);
        foreach($components as $component_name => $component_path){
            $content = preg_replace("/(<$component_name-component) data=\"(.*)\"\/>/", '@$render(\'' . trim($component_path) . '\', $2);', $content);
        }

        return $content;
    }

    public static function parsePhpTags(string $content): string
    {
        $patterns = [
            '@',
            '*}',
        ];
        $replacements = [
            '<?php ',
            ' ?>',
        ];
        return str_replace($patterns, $replacements, $content);
    }

    public static function parseShortcuts(string $content): string
    {
        $patterns = [
            '{{',
            '}}',
            '[[',
            ']]',
            '[{',
            '}]'
        ];
        $replacements = [
            '<?php $htmlOut(',
            '); ?>',
            '<?php echo $data[\'',
            '\']; ?>',
            '$data[\'',
            '\'] '
        ];
        return str_replace($patterns, $replacements, $content);
    }

    public static function parse(string $content, string $viewPath): string
    {
        $content = self::parseForsAndIfs($content);
        $content = self::parseComponents($content, $viewPath);
        $content = self::parseShortcuts($content);
        $content = self::parsePhpEndings($content);
        $content = self::parsePhpTags($content);
        
        $content = '?>' . $content . '<?php';
        return $content;
    }

    public static function render(MVC $mvc, string $view, mixed $data, bool $includeIndex): void
    {
        // define helper functions for view rendering
        $render = function (string $view, mixed $data) use ($mvc) {
            self::render($mvc, $view, $data, false);
        };
        $htmlOut = function (mixed $value) {
            echo nl2br(htmlentities($value));
        };
        $beginForm = function (string $controller, string $action, array $params = [], string $method = 'get', ?string $cssClass = null) use ($mvc) {
            $cc = $cssClass !== null ? " class=\"$cssClass\"" : '';
            echo "<form method=\"$method\" action=\"?\"$cc>";
            foreach ($params as $name => $value) {
                echo ("<input type=\"hidden\" name=\"$name\" value=\"$value\">");
            }
            echo "<input type=\"hidden\" name=\"{$mvc->getControllerParameterName()}\" value=\"$controller\">";
            echo "<input type=\"hidden\" name=\"{$mvc->getActionParameterName()}\" value=\"$action\">";
        };
        $endForm = function () {
            echo '</form>';
        };
        $link = function (string $content, string $controller, string $action, array $params = [], ?string $cssClass = null) use ($mvc, $htmlOut) {
            $cc = $cssClass != null ? " class=\"$cssClass\"" : '';
            $url = $mvc->buildActionLink($controller, $action, $params);
            echo "<a href=\"$url\"$cc>";
            $htmlOut($content);
            echo '</a>';
        };

        $url = function(string $controller, string $action, array $params = []) use ($mvc){
            $url = $mvc->buildActionLink($controller, $action, $params);
            echo $url;
        };

        $content = file_get_contents($mvc->getViewPath() . "$view-component/.html");
        $content = '<style>' . file_get_contents($mvc->getViewPath() . "$view-component/.css") . '</style>' . '<script>' . file_get_contents($mvc->getViewPath() . "$view-component/.js") . '</script>' . $content;
        if($includeIndex){
            $indexParts = explode("<routing-component />", file_get_contents($mvc->getViewPath() . 'index.html'));
            $content = $indexParts[0] . $content . $indexParts[1];
        }
        $content = self::parse($content, $mvc->getViewPath());
        // file_put_contents($mvc->getViewPath() . "$view.inc.data", $content);
        eval($content);
    }
}
