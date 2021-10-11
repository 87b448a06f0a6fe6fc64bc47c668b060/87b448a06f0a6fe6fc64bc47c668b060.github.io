var playerName, playerPic, score = 0, entryPointData; 


FBInstant.initializeAsync()
  .then(function(){

    // Start loading game assets here
    var progress = 0; 

    var interval = setInterval(function(){
      progress += 5; 
      FBInstant.setLoadingProgress(progress);

      if(progress >= 95){
        clearInterval(interval)

        FBInstant.startGameAsync()
          .then(function() {
            console.log("Game has started")


            // Retrieving context and player information can only be done
            // once startGameAsync() resolves
            var contextId = FBInstant.context.getID();
            var contextType = FBInstant.context.getType();
            var playerId = FBInstant.player.getID();
            playerPic = FBInstant.player.getPhoto();
            playerName = FBInstant.player.getName();
            entryPointData = FBInstant.getEntryPointData();

               
            console.log('Player ID: ' + playerId + "\nPlayer Name: " + playerName + "\nContext Type: "  + contextType + '\nContext ID: ' + contextId + '\nEntry Point Data: ' + entryPointData); 
          });
      }
      
    },100)

    console.log("loaded");

  }
);

//shortcuts - only work for android i believe
// FBInstant.canCreateShortcutAsync()
// .then(function(canCreateShortcut)
// {
//     if (canCreateShortcut)
//     {
//         FBInstant.createShortcutAsync()
//         .then(function() {
//             console.log('shortcut was created');
//         })
//         .catch(function(error) {
//             console.log('Something went wrong, possibly the user cancelled');
//         });
//     }
//     else
//     {
//         console.log('Cannot create a shortcut')
//     }
// });



function getel(id) {
    return document.getElementById(id);
}

var context_id = FBInstant.context.getID();
var context_type = FBInstant.context.getType();

var playwithFriend_btn = getel("playWithFriend")
var startButton = getel("startGame")
var gamePlay = getel("gamePlay")
var endButton = getel("endGame")

var playButtons = document.getElementsByClassName("playbutton");

var rock = getel("rock")
var paper = getel("paper")
var sciss = getel("scissors")

var buttongroup = getel("buttongroup") //group of the buttons

var choices = ["rock","scissors","paper"]

var status = getel("choice");
var gamestatus = getel("gamestatus"); 
var playerSc = getel("playerSc");

var playername = getel("playername"); 

var compSc = getel("compSc");
var compChooses = getel("compChooses");
var compChoosesParent = getel("compChoosesParent");

//if user plays rock and computer plays sciss user wins (for the first)
var wins = {
    "rock": "scissors",
    "scissors": "paper",
    "paper": "rock"
}

var playerScore = 0;
var compScore = 0;

startButton.addEventListener('click',startSoloGame);
endButton.addEventListener('click', exit); 
playwithFriend_btn.addEventListener('click', playwithFriend); 

Array.from(playButtons).forEach(btn => {
    btn.addEventListener("click",buttonClicked);
});


function playwithFriend(){

    playername.innerHTML = playerName + ":"; 

    hideStartButton(); 
    hidePlayWithFriendButton(); 
    

    //This function is how you switch context i.e. SOLO play to THREAD play
    //This is what creates the pop-up menu to select a friend to play
    FBInstant.context.chooseAsync()
    .then(function() {
        console.log("Switched context - now playing with a friend"); 
        var newtype = FBInstant.context.getType(); 
        var newid = FBInstant.context.getID(); 

        console.log('New context type' + newtype + '\nNew Context ID: ' +  newid);

        threadGamePlay(); 

    }).catch(function(error) {
        console.log('error');
    });

}

function threadGamePlay(){
    console.log('Now in thread play');
    var image64 = "data:image/png;base64,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"; 

    //This is just a sample of data that will be passed from the game to the context update message
    //Note that this isnt relevant to the game but to make a turn-based game this is where you would 
    //put the info that needs to be passed for each turn 
    var state_data = {
        id: FBInstant.player.getID(),
        level: 10,
        score: 1234
    };
     
    //This is the function that sends the context update message (aka game update as fb calls it)
    //You need to have "custom_update_template" in the fbapp-config.json file
    //See the mrmop link for an explanation of each of these parameters
    FBInstant.updateAsync({
        action: "CUSTOM",
        cta: "Play",
        image: image64,
        text: {
            default: playerName + " is challenging you to a RPS match",
        },
        template: "start_game",
        data: { myReplayData: state_data },
        strategy: "IMMEDIATE",
        notification: "NO_PUSH",
    })
    .then(function(){
        // Message was sent
        console.log('Message was sent successfully'); 
    }).catch(function(error) {
        // Error sending message
    });
}


function startSoloGame() {

    //Testing getEntryPointData() aka data that is passed from the bot to the game
    if(entryPointData != null){
        var myReplayData = entryPointData["myReplayData"]; 
        score = myReplayData["score"]; 
        console.log("score: " + score); 
    }


    console.log("Score is " + score); 
    if (score != 0){
        console.log("Changing player score from " + playerScore); 
        playerScore = score; 
        console.log("Player score is now " + playerScore); 
    }

    updateScore(); 

    hidecompChooses();
    hideStartButton(); 
    hidePlayWithFriendButton();

    showGamePlay();
    showButtons();

    playername.innerHTML = playerName + ":"; 

}

function playChoice(playerChoice) {
    var compChoice = choices[Math.floor(Math.random() * choices.length)];
    displayComputerChoice(compChoice);

    if(wins[playerChoice] == compChoice){ //win for player
        playerScore++
    } else if(compChoice == playerChoice) {
        //same symbol
    }
    else {
        compScore++
    }
    updateScore();

    if(playerScore == 3 || compScore == 3){
        var checkwon = false; 
        var numwins; 

        endGame(); 

        if(playerScore == 3){  
            checkwon = true;
        }

        //This is the function that will pass data from game to bot
        FBInstant.setSessionData({
                won:checkwon,
                image:playerPic, 
            });
    }

}


//The rest of these functions are just related to the game not facebook api
function displayComputerChoice(ch){
    showcompChooses()
    var cmpch = ""
    switch(ch){
        case "paper": cmpch = "✋"; break;
        case "rock": cmpch = "✊"; break;
        case "scissors": cmpch = "✌"; break;
    }
    compChooses.innerHTML = cmpch
}

function endGame(){
    gamestatus.innerHTML = "Game Over"; 
    hideButtons();  
    hidecompChooses();
}

function exit(){
    FBInstant.quit(); 
}

function updateScore() {
    playerSc.innerHTML = playerScore
    compSc.innerHTML = compScore
}

function buttonClicked(e){
    var btnChoice = e.target.id;
    playChoice(btnChoice);

}

function showButtons() {
    if(buttongroup.classList.contains("hidden")){
        buttongroup.classList.remove("hidden")
    }
}

function hideButtons() {
    if(!buttongroup.classList.contains("hidden")){
        buttongroup.classList.add("hidden")
    }
}

function hideStartButton() {
    if(!startButton.classList.contains("hidden")){
        startButton.classList.add("hidden")
    }
}

function hidePlayWithFriendButton() {
    if(!playWithFriend.classList.contains("hidden")){
        playWithFriend.classList.add("hidden")
    }
}
function showStartButton() {
    if(startButton.classList.contains("hidden")){
        startButton.classList.remove("hidden")
    }
}

function hideGamePlay() {
    if(!gamePlay.classList.contains("hidden")){
        gamePlay.classList.add("hidden")
    }
}

function showGamePlay() {
    if(gamePlay.classList.contains("hidden")){
        gamePlay.classList.remove("hidden")
    }
}

function hidecompChooses() {
    if(!compChoosesParent.classList.contains("hidden")){
        compChoosesParent.classList.add("hidden")
    }
}

function showcompChooses() {
    if(compChoosesParent.classList.contains("hidden")){
        compChoosesParent.classList.remove("hidden")
    }
}