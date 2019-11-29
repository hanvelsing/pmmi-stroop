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
        switch (colorName) {
            case 'blue':
                return 0;
            case 'green':
                return 1;
            case 'red':
                return 2;
            default:
                return -1;
        }
    }

    // Return random index of given array, or -1 if array is empty or invalid
    function getRandomIndex(array) {
        if (Array.isArray(array) && array.length > 0) {
            return Math.round(Math.random() * (array.length-1));
        } else {
            return -1;
        }
    }

    // Returns true if given key matches given color number, false if it doesn't.
    function isCorrectKeyInput(pressedKey, colNum) {
        pressedKey = jsPsych.pluginAPI.convertKeyCodeToKeyCharacter(pressedKey);
        if (colNum > keys.length) {
            return false;
        } else {
            return (pressedKey == keys[colNum]);
        }
    }


    const colors = [
        'blue',
        'green',
        'red'
    ];

    const keys = [
      'a',
      'w',
      'd'
    ];

    const colorNames = [
      'blau',
      'grün',
      'rot'
    ];

    const neutralWords = [ 'Papier', 'Haus', 'Anker', 'Stuhl', 'Fahrzeug', 'Pflanze', 'Stein', 'Lampe', 'Schrank',
        'Tür', 'Straße', 'Stadt', 'Flasche', 'Schal', 'Schuhe', 'Fenster'];

    const emotionalWords = [ 'Mord', 'Krieg', 'Tod', 'Grab', 'Schulden', 'Stress', 'Ekel', 'Prüfung', 'Diebstahl',
        'Zerstörung', 'Kälte', 'Panik', 'Hunger', 'Ebola', 'Bombe', 'Krankheit'];

    const neutralTestWords = ['Tuch', 'Würfel', 'Glas', 'Stift'];

    const emotionalTestWords = ['Unfall', 'Trauer', 'Verspätung', 'Unglück'];

    var d = new Date();

    var t_date = d.toLocaleDateString();
    var t_time = d.toLocaleTimeString();

    var part_id='';
    var exp_id='';

    var ids  = {
        type: 'survey-text',
        questions: [{prompt: "Participant ID"}, {prompt: "Experimenter ID"}],
        on_finish: function(data) {
            part_id=JSON.parse(data.responses)['Q0'];
            exp_id=JSON.parse(data.responses)['Q1'];
        }
    };


    var welcome = {
        type: 'html-keyboard-response',
        stimulus: function() {
            let text = '<p>Willkommen bei unserem Experiment.</p>';
            text += '<p>Wir werden deine Reaktionszeiten auf Wörter in verschiedenen Farben testen.</p>';
            text += '<p>Drücke eine beliebige Taste um die Einführung zu starten...</p>';
            return text;
        },
        response_ends_trial: true,
    };

    var testStartMsg = {
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

    var mainStartMsg = {
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

    var endMsg = {
        type: 'html-keyboard-response',
        stimulus: 'Ende, drücke eine beliebige Taste um die Ergebnisse anzuzeigen',
        response_ends_trial: true,
    };

    var feedback = {
        type: 'html-keyboard-response',
        trial_duration: 4000,
        response_ends_trial: false,
        stimulus: '<p><b>=== Falsche Taste gedrückt === </b></p>',
    };

    var fixation = {
        type: 'html-keyboard-response',
        trial_duration:500,
        response_ends_trial: false,
        stimulus: 'xxxxx',
    };

    var maintl=[];

    // Initialize arrays for unused word indexes
    var unusedNeutralIndexes = [];
    for(i = 0; i < neutralWords.length; i++) {
        unusedNeutralIndexes.push(i);
    }
    var unusedEmotionalIndexes = [];
    for(i = 0; i < emotionalWords.length; i++) {
        unusedEmotionalIndexes.push(i);
    }

    /**
     * Main timeline setup
     * This creates a [fixation, stim, feedback] block for each word in a random color and pushes them on the timeline.
     * It uses an array of used word-indexes to randomly select words without duplicates,
     * while saving the used words indexes in the const word array.
     */
    while ((unusedNeutralIndexes.length + unusedEmotionalIndexes.length) > 0) {
        //Select cond (emotional = 1, neutral = 2)
        var cond = 0;
        if(unusedNeutralIndexes.length == 0) {
            // If all neutral words were used up, use emotional
            cond = 1;
        } else if(unusedEmotionalIndexes.length == 0) {
            // If all emotional words were used up, use neutral
            cond = 2;
        } else {
            // If neither are used up, randomly assign 1 or 2
            cond = Math.round(Math.random()+1)
        }

        // Select word
        var word;
        var wordIndex;

        let randomNumber;
        switch (cond) {
            case 1:
                //Get and remove random emotional word index
                randomNumber = getRandomIndex(unusedEmotionalIndexes);
                wordIndex = unusedEmotionalIndexes[randomNumber];
                unusedEmotionalIndexes.splice(randomNumber, 1);
                // Get word for chosen index
                word = emotionalWords[wordIndex];
                break;
            case 2:
                // Get and remove random neutral word index
                randomNumber = getRandomIndex(unusedNeutralIndexes);
                wordIndex = unusedNeutralIndexes[randomNumber];
                unusedNeutralIndexes.splice(randomNumber, 1);
                // Get word for chosen index
                word = neutralWords[wordIndex];
                break;
            default:
                // Optional error handling
                wordIndex = -1;
                word = 'oops';
                break;
        }

        // Select random color
        var color = colors[getRandomIndex(colors)];

        var text='<span style="color:'+color+'">'+word+'</span>';
        var stim ={
            type: 'html-keyboard-response',
            stimulus: text,
            choices:['a','w','d'],
            trial_duration:1750,
            response_ends_trial: false,
            // cond + wordIndex is unique word identifier
            data: {
                v_cond: cond,
                v_wordnum: wordIndex,
                v_colnum: colorToNumber(color),
                v_correct: -999
            },
            on_finish: function(data) {
                pressed=data['key_press'];
                colnum=data['v_colnum'];
                if (pressed!=null) {
                    if (isCorrectKeyInput(pressed, colnum))  {
                        data['v_correct']=1;
                        jsPsych.endCurrentTimeline();
                    } else {
                        data['v_correct']=0;
                    }
                } else {
                    data['v_correct']=-999;
                    data['key_press']=-999;
                }
            }
        };

        var subtl = {
            timeline: [fixation, stim, feedback]
        };

        maintl.push(subtl);
    }

    /**
     * Test timeline setup.
     * Dynamically creates objects depending on fields colorNames[], colors[] and keys[].
     * Adds a short tutorial, checking if all required buttons are working.
     * Then creates [fixation, stimulus, feedback] block for each test word in a random color.
     */
    var testtl = [];
    var colorTutorialTl = [];

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

    /**
     * Adds emotional test words to the test timeline.
     */
    for (let i = 0; i < emotionalTestWords.length; i++) {
        //Set condition (1 = emotional)
        let cond = 1;

        // Select word
        let word = emotionalTestWords[i];
        let wordIndex = i;

        // Select random color
        let color = colors[getRandomIndex(colors)];

        let text='<span style="color:'+color+'">'+word+'</span>';
        let stim ={
            type: 'html-keyboard-response',
            stimulus: text,
            choices:['a','w','d'],
            trial_duration:1750,
            response_ends_trial: false,
            // cond + wordIndex is unique word identifier
            data: {
                v_cond: cond,
                v_wordnum: wordIndex,
                v_colnum: colorToNumber(color),
                v_correct: -999
            },
            on_finish: function(data) {
                pressed=data['key_press'];
                colnum=data['v_colnum'];
                if (pressed!=null) {
                    if (isCorrectKeyInput(pressed, colnum))  {
                        data['v_correct']=1;
                        jsPsych.endCurrentTimeline();
                    } else {
                        data['v_correct']=0;
                    }
                } else {
                    data['v_correct']=-999;
                    data['key_press']=-999;
                }
            }
        };

        let subtl = {
            timeline: [fixation, stim, feedback]
        };

        testtl.push(subtl);
    }

    /**
     * Adds neutral words to test timeline.
     */
    for (let i = 0; i < neutralTestWords.length; i++) {
        //Set condition (2 = neutral)
        let cond = 2;

        // Select word
        let word = neutralTestWords[i];
        let wordIndex = i;

        // Select random color
        let color = colors[getRandomIndex(colors)];

        let text='<span style="color:'+color+'">'+word+'</span>';
        let stim ={
            type: 'html-keyboard-response',
            stimulus: text,
            choices:['a','w','d'],
            trial_duration:1750,
            response_ends_trial: false,
            // cond + wordIndex is unique word identifier
            data: {
                v_cond: cond,
                v_wordnum: wordIndex,
                v_colnum: colorToNumber(color),
                v_correct: -999
            },
            on_finish: function(data) {
                pressed=data['key_press'];
                colnum=data['v_colnum'];
                if (pressed!=null) {
                    if (isCorrectKeyInput(pressed, colnum))  {
                        data['v_correct']=1;
                        jsPsych.endCurrentTimeline();
                    } else {
                        data['v_correct']=0;
                    }
                } else {
                    data['v_correct']=-999;
                    data['key_press']=-999;
                }
            }
        };

        let subtl = {
            timeline: [fixation, stim, feedback]
        };

        testtl.push(subtl);
    }

    maintl = jsPsych.randomization.shuffle(maintl);

    testtl = jsPsych.randomization.shuffle(testtl);

    maintl = [ids].concat(welcome, colorTutorialTl, testStartMsg, testtl, mainStartMsg,  maintl, endMsg);


    jsPsych.init({
        timeline: maintl,
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
            saveData('data-'+ part_id + '.csv', expData);
        }
    });

</script>
</html>
