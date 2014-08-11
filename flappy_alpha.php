<!DOCTYPE html>
<html>
    <head>
        <title>MULTIMEDIA FLAPPY BIRD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body id='theBody' onkeydown='keyHandler();' onclick="initialize();" style='padding:0px; margin:0px;'>
        <div style="float:right; margin-right:100px;">
            <h1>Leader Board</h1>
            <table id="leaderBoard" style="border: #000099 solid thick;">
                <tr><th>AKA</th><th>Last Name</th><th>Score</th><th>Date</th></tr>



            </table>
        </div>
        <script  type='text/javascript' src='../../js/vendor/modernizr-2.6.2.min.js'></script>
        <script  type='text/javascript' src='../../js/vendor/jquery-2.0.3.min.js'></script>
        <script type="text/javascript">
        /**
         * First defined the functional objects
         * 
         * @returns {Bird}
         */
        Bird = function() {
            this.posX = 150;
            this.posY = 200;
            this.birdImage = new Image();
            this.birdImage.src = 'img/flappy_bird2.png';
            this.getYpos = function() {
                return this.posY;
            };
            this.initialize = function() {
                return this.posY = 200;
            };
            this.move = function(amount) {
                this.posY = this.posY + amount;
                context.drawImage(this.birdImage, this.posX, this.posY);
            };
            this.die = function() {

                context.drawImage(this.birdImage, this.posX, canvas.height - birdHeight);
            };
        };
        Pillar = function() {
            this.posX = canvas.width;
            this.posY = 1 - Math.floor((Math.random() * 270));
            // this.posY = 0; // cheat mode
            this.pillarImage = new Image();
            this.pillarImage.src = 'img/pillar_long.png';
            this.getYpos = function() {
                return this.posY;
            };
            this.getXpos = function() {
                return this.posX;
            };
            this.move = function(amount) {
                this.posX = this.posX - amount;
                context.drawImage(this.pillarImage, this.posX, this.posY);
            };
        };

        // The excutable code starts here
        // The following 5 lines creates and adds HTML5 canvas with 
        // specific size to the body of the document
        var canvas = document.createElement("canvas");
        var context = canvas.getContext("2d");
        canvas.width = 800;
        canvas.height = 600;
        document.body.appendChild(canvas);

        //These are constants that apply to the physical images 
        var birdHeight = 40;
        var pillarHeight = 877;
        var holeStart = 365;
        var holeEnd = 482;
        var highScore = 0;
        //The baackground image
        var bgImage = new Image();
        bgImage.src = "img/background.jpg";
        //Stores all pillars
        var pillarArray = new Array();
        //The actual flappy
        var bird = new Bird();
        // Will be true if the game is over
        var gameOver = true;
        // Stores the number of the pillar closest to the bird
        var currentPillar;
        // store the time intervals
        var count;
        // store the amount of pillar cleared
        var score;
        // store the amount of time the bird should move up 
        var up;

        /**
         * Reset main playing variables
         * Note function is called immediately after it is defined 
         * it is also called in the fly method after game is over
         * 
         * @returns {undefined}
         */
        function initialize() {
            var karma = false;
            $.ajaxSetup({async: false});
            $.post("../../processing/do_game_score.php"
                    , {"game_name": "flappy_bird", "points": 0, type: "karma"}
            , function(data) {
                if (data[0] > 0) {//enough karma points so play
                    karma = true;
                }

            }, 'json');
            $.ajaxSetup({async: true});
            if (!karma) {
                window.open("http://mmedia.cput.ac.za/pmaster/index.php");
            }
            if (gameOver) {
                gameOver = false;
                currentPillar = 0;
                pillarArray.length = 0;
                count = 0;
                score = 0;
                up = 5;
                bird.initialize();
                //This is a very important line this calls the snapShot method 
                //atfer 10ms
                window.setTimeout(snapShot, 10);
            } 


        }
        initialize();

        /**
         * This function could also be called render
         * Display the game board everytime achieved
         * clearing the canvas when drawing the background image
         * 
         * @returns {unresolved}
         */
        function snapShot() {
            context.drawImage(bgImage, 0, 0);
            //Move all pillars along
            for (var j = 0; j < pillarArray.length; j++) {
                pillarArray[j].move(1);
            }

            //Create new pillars every 
            if (count % 270 === 0 && count >= 270) {
                var pillar2 = new Pillar();
                pillarArray.push(pillar2);
                currentPillar = pillarArray.length - 3;
            }

            if (gameOver) {
                bird.die();
                displayFinalScore();
                return;
            } else {
                if (up < 5) {
                    bird.move(-10);
                    up++;
                } else {
                    bird.move(1);
                }
            }
            count++;
            if (check4collision()) {
                console.log(" DEAD 2 !!! ");
                gameOver = true;
            }
            window.setTimeout(snapShot, 10);
        }
        /**
         * Checks for collisions with
         * 1) ground 
         * 2) top of screen
         * 3) top of current pillar
         * 4) bottom of current pillar
         * 
         * @returns {Boolean}
         */
        function check4collision() {
            var collision = false;
            if (bird.getYpos() > canvas.height - birdHeight) {
                console.log("Died bird to low ");
                collision = true;
            }
            if (bird.getYpos() < 0) {
                console.log("Died bird to high " + score);
                collision = true;
            }
            if (pillarArray[currentPillar] !== undefined) {
                if (pillarArray[currentPillar].getXpos() < 100 || pillarArray[currentPillar].getXpos() > 200) //check for Safe area
                {
                    //     console.log(" Safe!!! " + pillarArray[currentPillar].getXpos());
                } else {
                    if (pillarArray[currentPillar].getYpos() + holeStart > bird.getYpos()) {
                        console.log("Died into pillar Top " + currentPillar + " y " + bird.getYpos() + " P " + pillarArray[currentPillar].getYpos());
                        collision = true;
                    }
                    if (pillarArray[currentPillar].getYpos() + holeEnd < bird.getYpos() + birdHeight) {
                        console.log("Died into pillar bottom " + currentPillar + " y " + bird.getYpos() + " P " + pillarArray[currentPillar].getYpos());
                        collision = true;
                    }
                }
                score = currentPillar + 1;
            } else {
                //   score = 0;
            }
            drawScore();
            return collision;
        }
        function keyHandler() {
            if (!event)
                var event = window.event; // cross-browser shenanigans
            if (event.keyCode === 32) { // this is the spacebar
                up = 0;
            }
            return true; // treat all other keys normally;
        }

        /**
         * Draws the score while playing
         * @returns {undefined}
         */
        function drawScore() {
            context.font = '60pt Calibri';
            context.lineWidth = 3;
            context.strokeStyle = 'blue';
            context.strokeText(score, canvas.width / 2, canvas.height * 0.33);
        }
        function displayFinalScore() {
            var boxX = 280;
            var boxY = 110;
            context.beginPath();
            context.rect(canvas.width / 2 - boxX / 2, canvas.height / 2 - boxY * 2, boxX, boxY);
            context.fillStyle = 'yellow';
            context.fill();
            context.lineWidth = 7;
            context.strokeStyle = 'black';
            context.stroke();
            context.beginPath();
            //drawScore(score);
            $.post("../../processing/do_game_score.php"
                    , {"game_name": "flappy_bird", "points": score, type: "new_score"}
            , function(data) {
                context.lineWidth = 1;
                context.font = '28pt Calibri';
                context.strokeText("Score " + score, canvas.width / 2 - 120, canvas.height * 0.19);
                context.strokeText("Personal Best " + data[0], canvas.width / 2 - 120, canvas.height * 0.24);
                context.strokeText("Overall Best " + data[1], canvas.width / 2 - 120, canvas.height * 0.29);
            }, 'json');



        }
        </script>
    </body>
</html>
