<!DOCTYPE html>
<html>
<head>
  <title>My experiment</title>
  <script src="jspsych/jspsych.js"></script>
  <script src="jspsych/plugins/jspsych-html-keyboard-response.js"></script>
  <script src="jspsych/plugins/jspsych-survey-text.js"></script>
  <link href="jspsych/css/jspsych.css" rel="stylesheet" type="text/css"/>
</head>
<body></body>
<script>

  function saveData(name, data) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'write_data.php');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify({filename: name, filedata: data}));
  }

  var colour = [
    'red',
    'blue',
    'green',
  ];

  var word = [
    'black',
    'blue',
    'green',
  ];

  var d = new Date();

  var t_date = d.toLocaleDateString();
  var t_time = d.toLocaleTimeString();

  var part_id = '';
  var exp_id = '';

  var ids = {
    type: 'survey-text',
    questions: [{prompt: "Participant ID"}, {prompt: "Experimenter ID"}],
    on_finish: function (data) {
      part_id = JSON.parse(data.responses)['Q0'];
      exp_id = JSON.parse(data.responses)['Q1'];
    }
  };

  var instr1 = {
    type: 'html-keyboard-response',
    stimulus: "<p>Willkommen bei dem Experimen es werden Wörter " +
        "auf dem Bildschirm erscheinen.</p><p>Wenn das Wort <strong>richtig</strong>, " +
        "ist drücke so schnell du kannst <strong>A</strong> .</p>" +
        "<p>Wenn das Wort <strong>falsch</strong>, ist drücke <strong>D</strong> " +
        "so schnell du kannst.</p>" +
        "<p>Drücke eine beliebige Taste um das Training zu beginnen.</p>",
    response_ends_trial: true,
  };

  var instr2 = {
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
    trial_duration: 500,
    response_ends_trial: false,
    stimulus: 'xxxxx',
  };

  var maintl = [];

  var wordnum = 0;

  var num_trials = 4;

  for (i = 1; i <= num_trials; i++) {
    if (Math.random() < .5) {
      var colnum = wordnum;
      var cond = 1;
    } else {

      var tempcol = [0, 1, 2];
      tempcol.splice(wordnum, 1);
      var colnum = tempcol[Math.floor(Math.random() * 2)];
      var cond = 2;
    }
    ;
    var text = '<span style="color:' + colour[colnum] + '">' + word[wordnum] + '</span>';
    var stim = {
      type: 'html-keyboard-response',
      stimulus: text,
      choices: ['a', 'w', 'd'],
      trial_duration: 1750,
      response_ends_trial: false,
      data: {
        v_cond: cond,
        v_wordnum: wordnum,
        v_colnum: colnum,
        v_correct: -999
      },
      on_finish: function (data) {
        pressed = data['key_press'];
        colnum = data['v_colnum'];
        if (pressed != null) {
          if ((pressed == 65 && colnum == 0) ||
              (pressed == 87 && colnum == 1) ||
              (pressed == 68 && colnum == 2)) {
            data['v_correct'] = 1;
            jsPsych.endCurrentTimeline();
          } else {
            data['v_correct'] = 0;
          }
        } else {
          data['v_correct'] = -999;
          data['key_press'] = -999;
        }
      }
    };

    var subtl = {
      timeline: [fixation, stim, feedback]
    };

    maintl.push(subtl);

    if (wordnum < 2) {
      wordnum++;
    } else {
      wordnum = 0
    }
    ;
  }
  ;

  maintl = jsPsych.randomization.shuffle(maintl);

  maintl = [ids].concat([instr1], maintl, [instr2]);

  jsPsych.init({
    timeline: maintl,
    on_finish: function () {
      jsPsych.data.addProperties({
        id: part_id,
        date: t_date,
        time: t_time
      });

      jsPsych.data.displayData('csv');
      var expData = jsPsych.data.get().filterCustom(
          function (x) {
            return (x['v_cond'] == 1 || x['v_cond'] == 2)
          }).csv();
      saveData('data-' + part_id + '.csv', expData);
    }
  });

</script>
</html>
