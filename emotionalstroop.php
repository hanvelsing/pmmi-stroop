<!DOCTYPE html>
<html>
<head>
    <title>My experiment</title>
    <script src="jspsych/jspsych.js"></script>
    <script src="jspsych/plugins/jspsych-html-keyboard-response.js"></script>
    <script src="jspsych/plugins/jspsych-survey-text.js"></script>
    <link href="jspsych/css/jspsych.css" rel="stylesheet" type="text/css" />
</head>
<body></body>
<script>

    function saveData(name, data){
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'write_data.php');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({filename: name, filedata: data}));
    }

    // Return numerical code for colors
    function colorToNumber(colorName) {
        return colors.indexOf(colorName);
    }

    // Returns the expected response key to a given word color, -1 for invalid colors
    function getExpectedResponse(color) {
        let index = colors.indexOf(color);
        if (index < 0 || index > keys.length-1) {
            return -1;
        } else {
            return keys[index];
        }
    }

    /**
     * Colors to be used
     */
    const colors = [
        'blue',
        'green',
        'purple'
    ];

    /**
     * Expected keys, keys[n] is expected for colors[n]
     */
    const keys = [
      'a',
      'w',
      'd'
    ];

    /**
     * Localized color names, colorNames[n] is displayed name for colors[n]
     */
    const colorNames = [
      'blau',
      'grün',
      'lilan'
    ];

    /**
     * Time duration for fixation row in ms, randomly chosen for each trial if multiple values are given
     */
    const fixationDurations = [ 500 ];

    /**
     * Time duration for trials in ms
     */
    const trialDuration = 1750;

    /**
     * Neutral words to be used in experiment
     */
    const neutralWords = [ 'Papier', 'Haus', 'Anker', 'Stuhl', 'Fahrzeug', 'Pflanze', 'Stein', 'Lampe', 'Schrank',
        'Tür', 'Straße', 'Stadt', 'Flasche', 'Schal', 'Schuhe', 'Fenster'];

    /**
     * Emotional words to be used in experiment
     */
    const emotionalWords = [ 'Mord', 'Krieg', 'Tod', 'Grab', 'Schulden', 'Stress', 'Ekel', 'Prüfung', 'Diebstahl',
        'Zerstörung', 'Kälte', 'Panik', 'Hunger', 'Ebola', 'Bombe', 'Krankheit'];

    /**
     * Neutral words to be used in test phase
     */
    const neutralTestWords = ['Tuch', 'Würfel', 'Glas', 'Stift'];

    /**
     * Emotional words to be used in test phase
     */
    const emotionalTestWords = ['Unfall', 'Trauer', 'Verspätung', 'Unglück'];

    let d = new Date();

    let t_date = d.toLocaleDateString();
    let t_time = d.toLocaleTimeString();

    let part_id='';
    let exp_id='';

    let ids  = {
        type: 'survey-text',
        questions: [{prompt: "Participant ID"}, {prompt: "Experimenter ID"}],
        on_finish: function(data) {
            part_id=JSON.parse(data.responses)['Q0'];
            exp_id=JSON.parse(data.responses)['Q1'];
        }
    };

    // Welcome message
    let welcome = {
        type: 'html-keyboard-response',
        stimulus: function() {
            let text = '<p>Willkommen bei unserem Experiment.</p>';
            text += '<p>Wir werden deine Reaktionszeiten auf Wörter in verschiedenen Farben testen.</p>';
            text += '<p>Drücke eine beliebige Taste um die Einführung zu starten...</p>';
            return text;
        },
        response_ends_trial: true
    };

    // Message before test phase starts. Dynamically loads colors, keys and colorNames
    let testStartMsg = {
        type: 'html-keyboard-response',
        stimulus: function() {
            let numTestTrials = neutralTestWords.length + emotionalTestWords.length;
            let text = '<p> Nun werden dir ' + numTestTrials + ' Wörter in verschiedenen Farben angezeigt.</p>';
            text += '<p>Dies ist nur die Aufwärmrunde.</p>';
            text += '<p>Versuche so schnell wie möglich die richtige Taste zu drücken. Zur Erinnerung:</p>';
            for (let i = 0; i < colors.length; i++) {
                text += '<p style="color:' + colors[i] + '">' + colorNames[i] + 'es Wort: Drücke ' + keys[i].toUpperCase();
            }
            text += '<p>Drücke beliebige Taste um den Testdurchlauf zu starten...</p>';
            return text;
        },
        response_ends_trial: true
    };

    // Message before experiment starts. Dynamically loads colors, keys and colorNames
    let mainStartMsg = {
        type: 'html-keyboard-response',
        stimulus: function() {
            let numTrials = neutralWords.length + emotionalWords.length;
            let text = '<p> Nun kommen wir zum wirklichen Experiment.</p>';
            text += '<p> Dir werden ' + numTrials + ' Wörter in verschiedenen Farben angezeigt.</p>';
            text += '<p>Versuche so schnell wie möglich die richtige Taste zu drücken. Zur Erinnerung:</p>';
            for (let i = 0; i < colors.length; i++) {
                text += '<p style="color:' + colors[i] + '">' + colorNames[i] + 'es Wort: Drücke ' + keys[i].toUpperCase();
            }
            text += '<p>Nur dein erster Tastendruck pro Wort zählt.</p>';
            text += '<p>Versuche so wenige Fehler wie möglich zu machen.</p>';
            text += '<p>Drücke beliebige Taste um das Experiment zu starten...</p>';
            return text;
        },
        response_ends_trial: true
    };

    // Feedback text
    let feedback = {
        type: 'html-keyboard-response',
        trial_duration: 4000,
        response_ends_trial: false,
        stimulus: '<p><b>=== Falsche Taste gedrückt === </b></p>',
        data: {test_part: 'feedback'}
    };

    // Displays feedback text only if last input was not correct
    let conditionalFeedback = {
        timeline: [feedback],
        conditional_function: function(){
            let data = jsPsych.data.get().last(1).values()[0];
            return !(data.v_correct == 1);
        }
    };

    // Fixation row, display duration taken from fixationDurations
    let fixation = {
        type: 'html-keyboard-response',
        response_ends_trial: false,
        stimulus: 'xxxxx',
        trial_duration: function(){
            // Picks random entry of fixationDurations
            return jsPsych.randomization.sampleWithReplacement(fixationDurations, 1)[0];
        },
        data: {test_part: 'fixation'}
    };

    /**
     * Experiment timeline setup
     * This creates a [fixation, stim, conditionalFeedback] block for each word.
     */
    let experimentTimeline = [];

    // Data object for experiment stimuli, later loaded in via timeline_variables
    let experimentStimuli = [];
    for (let i = 0; i < emotionalWords.length; i++) {
        let color = colors[i % colors.length];
        let data = {test_part: 'main', expected_response: getExpectedResponse(color), v_cond: 1, v_wordNum: i,
            v_colNum: colorToNumber(color), v_correct: -999};
        experimentStimuli.push({ word: emotionalWords[i], color: color, data: data});
    }
    for (let i = 0; i < neutralWords.length; i++) {
        let color = colors[i % colors.length];
        let data = {test_part: 'main', expected_response: getExpectedResponse(color), v_cond: 2, v_wordNum: i,
            v_colNum: colorToNumber(color), v_correct: -999};
        experimentStimuli.push({ word: neutralWords[i], color: color, data: data});
    }

    // One experiment trial object
    let experimentTrial = {
        type: "html-keyboard-response",
        stimulus: function() {
            text = '<span style="color:';
            text += jsPsych.timelineVariable('color', true);
            text += '">';
            text += jsPsych.timelineVariable('word', true);
            text += '</span>';
            return text;
        },
        choices: keys,
        trial_duration: trialDuration,
        response_ends_trial: false,
        data: jsPsych.timelineVariable('data'),
        on_finish: function(data){
            if (data.key_press == null) {
                data.v_correct = -999;
                data.key_press = -999;
            } else if (data.key_press == jsPsych.pluginAPI.convertKeyCharacterToKeyCode(data.expected_response)) {
                data.v_correct = 1;
            } else {
                data.v_correct = 0;
            }
        },
    };

    // Main experimentTrials object, contains many experimentTrail objects with different experimentStimuli data
    let experimentTrials = {
        timeline: [fixation, experimentTrial, conditionalFeedback],
        timeline_variables: experimentStimuli,
        randomize_order: true
    };
    experimentTimeline.push(experimentTrials);

    /**
     * Test timeline setup.
     * Dynamically creates tutorial objects depending on fields colorNames[], colors[] and keys[].
     * Adds a short tutorial, checking if all required buttons are working.
     * Then creates [fixation, stimulus, conditionalFeedback] block for each test word.
     */
    /*
    * Color tutorial setup
    */
    let colorTutorialTl = [];
    // Dynamically adds colors, names and keys as variables
    let tutorialVariables = [];
    for (let i = 0; i < colors.length; i++) {
        tutorialVariables.push( { colorName: colorNames[i], color: colors[i], key: keys[i] });
    }

    let colorTutorial = {
        timeline: [
            {
                type: 'html-keyboard-response',
                stimulus: function() {
                    let colorName = jsPsych.timelineVariable('colorName', true);
                    let color = jsPsych.timelineVariable('color', true);
                    let key = jsPsych.timelineVariable('key', true);
                    key = key.toString().toUpperCase();
                    let text = '<p><strong>Tutorial</strong></p>';
                    text += '<p>Wenn du ein</p>';
                    text += '<p style="color:'+ color +'">'+ colorName + 'es Wort</p>';
                    text += '<p>siehst, drücke <strong>'+ key +'</strong></p>';
                    return text;
                },
                choices:[jsPsych.timelineVariable('key')],
                response_ends_trial: true,
            }
        ],
        timeline_variables: tutorialVariables
    };

    colorTutorialTl.push(colorTutorial);

    /*
    * Main test timeline setup
     */
    let testTimeline = [];
    // Load test stimuli into timeline_variable array
    let testStimuli = [];
    for (let i = 0; i < emotionalTestWords.length; i++) {
        let color = colors[i % colors.length];
        let data = {test_part: 'test', expected_response: getExpectedResponse(color), v_cond: 1, v_wordNum: i,
            v_colNum: colorToNumber(color), v_correct: -999};

        testStimuli.push({ word: emotionalTestWords[i], color: color, data: data});
    }
    for (let i = 0; i < neutralTestWords.length; i++) {
        let color = colors[i % colors.length];
        let data = {test_part: 'test', expected_response: getExpectedResponse(color), v_cond: 2, v_wordNum: i,
            v_colNum: colorToNumber(color), v_correct: -999};
        testStimuli.push({ word: neutralTestWords[i], color: color, data: data});
    }

    let testTrial = {
        type: "html-keyboard-response",
        stimulus: function() {
            let text = '<span style="color:';
            text += jsPsych.timelineVariable('color', true);
            text += '">';
            text += jsPsych.timelineVariable('word', true);
            text += '</span>';
            return text;
        },
        choices: keys,
        trial_duration: trialDuration,
        response_ends_trial: false,
        data: jsPsych.timelineVariable('data'),
        on_finish: function(data){
            if (data.key_press == null) {
                data.v_correct = -999;
                data.key_press = -999;
            } else if (data.key_press == jsPsych.pluginAPI.convertKeyCharacterToKeyCode(data.expected_response)) {
                data.v_correct = 1;
            } else {
                data.v_correct = 0;
            }
        },
    };

    // Main testTrials object
    let testTrials = {
        timeline: [fixation, testTrial, conditionalFeedback],
        timeline_variables: testStimuli,
        randomize_order: true
    };
    testTimeline.push(testTrials);

    /**
     * Debrief block setup, contains some info on participant performance
     */
    let debrief = {
        type: "html-keyboard-response",
        stimulus: function() {

            let trials = jsPsych.data.get().filter({test_part: 'main'});
            let correctTrials = trials.filter({v_correct: 1});
            let accuracy = Math.round(correctTrials.count() / trials.count() * 100);
            let reactionTime = Math.round(correctTrials.select('rt').mean());

            return "<p>Deine Genauigkeit war "+accuracy+"%.</p>"+
                "<p>Deine durchschnittliche Reaktionszeit war "+reactionTime+"ms.</p>"+
                "<p>Drücke eine beliebige Taste um die Ergebnisse anzuzeigen.</p>"+
                "<p>Vielen Dank für die Teilnahme!</p>"
        }
    };

    /**
     * Merge timelines into one
     */
    let mainTimeline = [ids].concat(welcome, colorTutorialTl, testStartMsg, testTimeline,
        mainStartMsg, experimentTimeline, debrief);

    /**
     * Start jsPsych
     */
    jsPsych.init({
        timeline: mainTimeline,
        on_finish: function() {
            jsPsych.data.addProperties({
                id: part_id,
                date: t_date,
                time: t_time
            });

            jsPsych.data.displayData('csv');
            var expData = jsPsych.data.get().filterCustom(
                function(x){
                    return (x['v_cond'] == 1 || x['v_cond'] == 2)
                }).csv();
            saveData('data-'+ part_id, expData);
        }
    });

</script>
</html>
