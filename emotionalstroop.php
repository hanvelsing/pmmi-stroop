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

    function colorToNumber(colorName) {
        switch (colorName) {
            case 'blue':
                return 0;
                break;
            case 'green':
                return 1;
                break;
            case 'red':
                return 2;
                break;
            default:
                return -1;
        }
    }

    function getRandomIndex(array) {
        // Return random index of given array, or -1 if array is empty or invalid
        if (Array.isArray(array) && array.length > 0) {
            return Math.round(Math.random() * (array.length-1));
        } else {
            return -1;
        }
    }


    var colors = [
         'blue',
         'green',
         'red'
    ];
    
    const neutralWords = [ 'Papier', 'Haus', 'Anker', 'Stuhl', 'Fahrzeug', 'Pflanze', 'Stein', 'Lampe', 'Schrank',
        'Tür', 'Straße', 'Stadt', 'Flasche', 'Schal', 'Schuhe', 'Fenster'];

    const emotionalWords = [ 'Mord', 'Krieg', 'Tod', 'Grab', 'Schulden', 'Stress', 'Ekel', 'Prüfung', 'Diebstahl',
        'Zerstörung', 'Kälte', 'Panik', 'Hunger', 'Ebola', 'Bombe', 'Krankheit'];

    const neutralTestWords = [];

    const emotionalTestWords = [];

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
    
    
    var instr1 = {
		type: 'html-keyboard-response',
		stimulus: 'Instruction',
		response_ends_trial: true,		
	};
	
	var instr2 = {
		type: 'html-keyboard-response',
		stimulus: 'Ending',
		response_ends_trial: true,		
	};

	var feedback = {
		type: 'html-keyboard-response',
		trial_duration:4000,
		response_ends_trial: false,		
		stimulus: '<p><b>=== Wrong button pressed === </b></p>',
	};
	
	var fixation = {
		type: 'html-keyboard-response',
		trial_duration:500,
		response_ends_trial: false,		
		stimulus: 'xxxxx',
	};

    var maintl=[];

	var wordnum = 0;
	
	var num_trials=4;

    // Initialize arrays for unused word indexes
    var unusedNeutralIndexes = [];
    for(i = 0; i < neutralWords.length; i++) {
        unusedNeutralIndexes.push(i);
    }
    var unusedEmotionalIndexes = [];
    for(i = 0; i < emotionalWords.length; i++) {
        unusedEmotionalIndexes.push(i);
    }

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
        var randomNumber;
        var word;
	    var wordIndex;
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
        var color = colors[Math.round(Math.random()*(colors.length-1))];

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
					if ((pressed==65 && colnum==0) ||
                        (pressed==87 && colnum==1) ||
                        (pressed==68 && colnum==2))  {
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
		
		if (wordnum < 3) {
			wordnum++;
		} else {
			wordnum = 0
		};
	};
	
	maintl = jsPsych.randomization.shuffle(maintl);
	
	maintl = [ids].concat([instr1],maintl, [instr2]);
	

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
