<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for patch hide social options.
 *
 * @package   mod_hvp
 * @copyright 2018 Andreas Wagner, ISB Bayern
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$formdata = (object) [
        'name' => 'test',
        'showdescription' => 0,
        'h5paction' => 'create',
        'h5pfile' => 75956846,
        'h5plibrary' => 'H5P.CoursePresentation 1.19',
        'h5pparams' => '{"presentation":{"slides":[{"elements":[],"keywords":[],"slideBackgroundSelector":{}}],"keywordListEnabled":true,"globalBackgroundSelector":{},"keywordListAlwaysShow":false,"keywordListAutoHide":false,"keywordListOpacity":90},"l10n":{"slide":"Schaubild","score":"Score","yourScore":"Deine Punktzahl","maxScore":"Maximale Punktzahl","goodScore":"Glückwunsch! Du hast @percent richtig!","okScore":"Nette Leistung! Du hast @percent richtig!","badScore":"Du hast @percent korrekt.","total":"Total","totalScore":"Total Score","showSolutions":"Lösungen","retry":"Wiederholen","title":"Titel","author":"Autor","lisence":"Lizenz","license":"Lizenz","exportAnswers":"Text exportieren","copyright":"Urheberrecht","hideKeywords":"Schlagwortliste verbergen","showKeywords":"Schlagwortliste anzeigen","fullscreen":"Vollbildmodus","exitFullscreen":"Vollbild beenden","prevSlide":"Vorheriges Schaubild","nextSlide":"Nächstes Schaubild","currentSlide":"Aktuelles Schaubild","lastSlide":"Letztes Schaubild","solutionModeTitle":"Lösungsmodus beenden","solutionModeText":"Lösungsmodus","summaryMultipleTaskText":"Mehrere Aufgaben","scoreMessage":"Du hast erreicht:","shareFacebook":"Auf Facebook teilen","shareTwitter":"Auf Twitter teilen","shareGoogle":"Auf Google+ teilen","summary":"Zusammenfassung","solutionsButtonTitle":"Kommentare zeigen","printTitle":"Drucken","printIngress":"Wie möchtest du diese Präsentation drucken?","printAllSlides":"Alle Schaubilder drucken","printCurrentSlide":"Aktuelles Schaubild drucken","noTitle":"No title","accessibilitySlideNavigationExplanation":"Use left and right arrow to change slide in that direction whenever canvas is selected","accessibilityCanvasLabel":"Presentation canvas. Use left and right arrow to move between slides.","containsNotCompleted":"@slideName contains not completed interaction","containsCompleted":"@slideName contains completed interaction","slideCount":"Slide @index of @total","containsOnlyCorrect":"@slideName only has correct answers","containsIncorrectAnswers":"@slideName has incorrect answers","shareResult":"Share Result","accessibilityTotalScore":"You got @score of @maxScore points in total"},"override":{"activeSurface":false,"hideSummarySlide":false,"enablePrintButton":false,"social":{"showFacebookShare":true,"facebookShare":{"url":"@currentpageurl","quote":"I scored @score out of @maxScore on a task at @currentpageurl."},"showTwitterShare":false,"twitterShare":{"statement":"I scored @score out of @maxScore on a task at @currentpageurl.","url":"@currentpageurl","hashtags":"h5p, Kurs"},"showGoogleShare":false,"googleShareUrl":"@currentpageurl"}}}',
        'frame' => 1,
        'export' => 1,
        'copyright' => 1,
        'gradecat' => 211,
        'gradepass' => 0,
        'maximumgrade' => 10,
        'visible' => 1,
        'visibleoncoursepage' => 1,
        'cmidnumber' => 0,
        'groupmode' => 0,
        'groupingid' => 0,
        'tags' => [],
        'coursemodule' => 1636,
        'section' => 1,
        'module' => 34,
        'modulename' => 'hvp',
        'instance' => 0,
        'add' => 'hvp',
        'update' => 0,
        'return' => 0,
        'sr' => 0,
        'competency_rule' => 0,
        'completion' => 0,
        'completionview' => 0,
        'completionexpected' => 0,
        'completiongradeitemnumber' => 0,
        'conditiongradegroup' => [],
        'conditionfieldgroup' => [],
        'intro' => '',
        'introformat' => 1,
        'disable' => 0,
        'params' => '{"presentation":{"slides":[{"elements":[],"keywords":[],"slideBackgroundSelector":{}}],"keywordListEnabled":true,"globalBackgroundSelector":{},"keywordListAlwaysShow":false,"keywordListAutoHide":false,"keywordListOpacity":90},"l10n":{"slide":"Schaubild","score":"Score","yourScore":"Deine Punktzahl","maxScore":"Maximale Punktzahl","goodScore":"Glückwunsch! Du hast @percent richtig!","okScore":"Nette Leistung! Du hast @percent richtig!","badScore":"Du hast @percent korrekt.","total":"Total","totalScore":"Total Score","showSolutions":"Lösungen","retry":"Wiederholen","title":"Titel","author":"Autor","lisence":"Lizenz","license":"Lizenz","exportAnswers":"Text exportieren","copyright":"Urheberrecht","hideKeywords":"Schlagwortliste verbergen","showKeywords":"Schlagwortliste anzeigen","fullscreen":"Vollbildmodus","exitFullscreen":"Vollbild beenden","prevSlide":"Vorheriges Schaubild","nextSlide":"Nächstes Schaubild","currentSlide":"Aktuelles Schaubild","lastSlide":"Letztes Schaubild","solutionModeTitle":"Lösungsmodus beenden","solutionModeText":"Lösungsmodus","summaryMultipleTaskText":"Mehrere Aufgaben","scoreMessage":"Du hast erreicht:","shareFacebook":"Auf Facebook teilen","shareTwitter":"Auf Twitter teilen","shareGoogle":"Auf Google+ teilen","summary":"Zusammenfassung","solutionsButtonTitle":"Kommentare zeigen","printTitle":"Drucken","printIngress":"Wie möchtest du diese Präsentation drucken?","printAllSlides":"Alle Schaubilder drucken","printCurrentSlide":"Aktuelles Schaubild drucken","noTitle":"No title","accessibilitySlideNavigationExplanation":"Use left and right arrow to change slide in that direction whenever canvas is selected","accessibilityCanvasLabel":"Presentation canvas. Use left and right arrow to move between slides.","containsNotCompleted":"@slideName contains not completed interaction","containsCompleted":"@slideName contains completed interaction","slideCount":"Slide @index of @total","containsOnlyCorrect":"@slideName only has correct answers","containsIncorrectAnswers":"@slideName has incorrect answers","shareResult":"Share Result","accessibilityTotalScore":"You got @score of @maxScore points in total"},"override":{"activeSurface":false,"hideSummarySlide":false,"enablePrintButton":false,"social":{"showFacebookShare":true,"facebookShare":{"url":"@currentpageurl","quote":"I scored @score out of @maxScore on a task at @currentpageurl."},"showTwitterShare":true,"twitterShare":{"statement":"I scored @score out of @maxScore on a task at @currentpageurl.","url":"@currentpageurl","hashtags":"h5p, Kurs"},"showGoogleShare":true,"googleShareUrl":"@currentpageurl"}}}',
        'library' => [
            'machineName' => 'H5P.CoursePresentation',
            'majorVersion' => 1,
            'minorVersion' => 19,
            'libraryId' => 83,
        ],
        'metadata' => ''
];
