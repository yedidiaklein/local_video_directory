initDraw(document.getElementById('canvas'));

//var offsets = document.getElementById('canvas').getBoundingClientRect();

function initDraw(canvas) {
    function setMousePosition(e) {
        var ev = e || window.event; //Moz || IE
        if (ev.pageX) { //Moz
            mouse.x = ev.offsetX;
            mouse.y = ev.offsetY;
            //mouse.x = ev.pageX - offsets.x; //+ window.pageXOffset - 35;
            //console.log(ev);
            //console.log(ev.layerX);
            //console.log(ev.offsetX);
            //mouse.y = ev.pageY - 230; // + window.pageYOffset - 230;
        } else if (ev.clientX) { //IE
            mouse.x = ev.clientX + document.body.scrollLeft - 35;
            mouse.y = ev.clientY + document.body.scrollTop - 230;
        }
    };

    var mouse = {
        x: 0,
        y: 0,
        startX: 0,
        startY: 0
    };
    var element = null;

    canvas.onmousemove = function (e) {
        setMousePosition(e);
        if (element !== null) {
            element.style.width = Math.abs(mouse.x - mouse.startX) + 'px';
            element.style.height = Math.abs(mouse.y - mouse.startY) + 'px';
            element.style.left = (mouse.x - mouse.startX < 0) ? mouse.x + 'px' : mouse.startX + 'px';
            element.style.top = (mouse.y - mouse.startY < 0) ? mouse.y + 'px' : mouse.startY + 'px';
        }
    }

    canvas.onclick = function (e) {
        if (element !== null) {
            element = null;
            canvas.style.cursor = "default";
            //console.log("finsihed.");
	        document.cookie = "mousex=" + mouse.x;
            document.cookie = "mousey=" + mouse.y;
            document.cookie = "mousestartx=" + mouse.startX;
            document.cookie = "mousestarty=" + mouse.startY;
            document.getElementById('rectangleData').innerHTML = '<b>Rectangle Data: </b>' + 
                'EndX: ' + mouse.x + ' EndY: ' + mouse.y + ' StartX: ' + mouse.startX + ' StartY: ' + mouse.startY +
                '<input type="hidden" name="EndX" value="' + mouse.x + '">' + 
                '<input type="hidden" name="EndY" value="' + mouse.y + '">' + 
                '<input type="hidden" name="StartX" value="' + mouse.startX + '">' + 
                '<input type="hidden" name="StartY" value="' + mouse.startY + '">';
        } else {
            //console.log("begun.");
	        var delElement = document.getElementById("rectangle");
	        if (delElement) {
	    	    delElement.parentNode.removeChild(delElement);
	        }
            mouse.startX = mouse.x;
            mouse.startY = mouse.y;
            element = document.createElement('div');
            element.className = 'rectangle';
	        element.setAttribute("id", "rectangle");
            element.style.left = mouse.x + 'px';
            element.style.top = mouse.y + 'px';
            canvas.appendChild(element)
            canvas.style.cursor = "crosshair";
        }
    }
}

    // Video
    var video = document.getElementById("video");
  
    // Buttons
    var playButton = document.getElementById("play-pause");
    var muteButton = document.getElementById("mute");
    var fullScreenButton = document.getElementById("full-screen");
  
    // Sliders
    var seekBar = document.getElementById("seek-bar");
    var volumeBar = document.getElementById("volume-bar");

  // Event listener for the play/pause button
playButton.addEventListener("click", function() {
    if (video.paused == true) {
      // Play the video
      video.play();
  
      // Update the button text to 'Pause'
      playButton.innerHTML = "Pause";
    } else {
      // Pause the video
      video.pause();
  
      // Update the button text to 'Play'
      playButton.innerHTML = "Play";
    }
  });

  // Event listener for the mute button
muteButton.addEventListener("click", function() {
    if (video.muted == false) {
      // Mute the video
      video.muted = true;
  
      // Update the button text
      muteButton.innerHTML = "Unmute";
    } else {
      // Unmute the video
      video.muted = false;
  
      // Update the button text
      muteButton.innerHTML = "Mute";
    }
  });

  // Event listener for the full-screen button
fullScreenButton.addEventListener("click", function() {
    if (video.requestFullscreen) {
      video.requestFullscreen();
    } else if (video.mozRequestFullScreen) {
      video.mozRequestFullScreen(); // Firefox
    } else if (video.webkitRequestFullscreen) {
      video.webkitRequestFullscreen(); // Chrome and Safari
    }
  });

  // Event listener for the seek bar
seekBar.addEventListener("change", function() {
    // Calculate the new time
    var time = video.duration * (seekBar.value / 100);
  
    // Update the video time
    video.currentTime = time;
  });

  // Update the seek bar as the video plays
video.addEventListener("timeupdate", function() {
    // Calculate the slider value
    var value = (100 / video.duration) * video.currentTime;
  
    // Update the slider value
    seekBar.value = value;
  });

  // Pause the video when the slider handle is being dragged
seekBar.addEventListener("mousedown", function() {
    video.pause();
  });
  
  // Play the video when the slider handle is dropped
  seekBar.addEventListener("mouseup", function() {
    video.play();
  });

  // Event listener for the volume bar
volumeBar.addEventListener("change", function() {
    // Update the video volume
    video.volume = volumeBar.value;
  });
