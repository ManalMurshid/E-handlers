const videoElement = document.getElementById('videoElement');
const canvasElement = document.getElementById('canvasElement');
const audioElement = document.getElementById('audioElement');

let videoStream, audioStream, mediaRecorder;
let audioChunks = [];
let webcamAllowed = false;
let microphoneAllowed = false;
let secondaryCamAllowed = false;

async function startWebcam() {
    const primary_camera_input_text = document.getElementById('primary_camera_input_text');
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
        if (primary_camera_input_text) primary_camera_input_text.innerHTML = "ALLOWED";
        videoElement.srcObject = videoStream;
        webcamAllowed = true;
        monitorPermissions();
    } catch (error) {
        console.error('Error accessing webcam:', error);
        if (primary_camera_input_text) primary_camera_input_text.innerHTML = "NOT ALLOWED";
        alert('Error accessing webcam. Please ensure it is connected and permissions are granted.');
    }
    checkPermissions();
}

function capturePhoto() {
    if (webcamAllowed && videoStream && videoStream.getVideoTracks().length > 0 && videoStream.getVideoTracks()[0].enabled) {
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;
        canvasElement.getContext('2d').drawImage(videoElement, 0, 0);
        const photoDataUrl = canvasElement.toDataURL('image/jpeg');

        // Send the image data to the server
        fetch('save_image.php', {
            method: 'POST',
            body: JSON.stringify({ image: photoDataUrl }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Error saving snapshot:', error);
            alert('Error saving snapshot. Please try again later.');
        });
    } else {
        alert('Webcam permission is required.');
    }
}

async function startMicrophone() {
    const microphone_input_text = document.getElementById('microphone_input_text');
    try {
        audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        if(microphone_input_text) microphone_input_text.innerHTML = "ALLOWED";
        mediaRecorder = new MediaRecorder(audioStream);
        mediaRecorder.ondataavailable = function (event) {
            audioChunks.push(event.data);
        };
        mediaRecorder.onstop = function () {
            const audioBlob = new Blob(audioChunks, { type: 'audio/mp3' });
            audioChunks = [];
            const audioUrl = URL.createObjectURL(audioBlob);
            audioElement.src = audioUrl;

            // Send the audio data to the server
            const formData = new FormData();
            formData.append('audio', audioBlob, `audio_${Date.now()}.mp3`);

            fetch('save_audio.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
            })
            .catch(error => {
                console.error('Error saving audio:', error);
                alert('Error saving audio. Please try again later.');
            });
        };
        microphoneAllowed = true;
        monitorPermissions();
    } catch (error) {
        console.error('Error accessing microphone:', error);
        if(microphone_input_text) microphone_input_text.innerHTML = "NOT ALLOWED";
        alert('Error accessing microphone. Please ensure it is connected and permissions are granted.');
    }
    checkPermissions();
}

function startRecording() {
    mediaRecorder.start();
    setTimeout(() => {
        mediaRecorder.stop();
    }, 5000); // Record for 5 seconds
}


// Function to update PHP session with webcamAllowed status
function updateWebcamAllowedStatus(allowed) {
    // Send AJAX request to update PHP session variable
    $.ajax({
        url: 'update-session.php',
        method: 'POST',
        data: { webcamAllowed: allowed },
        success: function (response) {
            console.log('Webcam permission set in session.');
        },
        error: function (xhr, status, error) {
            console.error('Error setting webcam permission in session.');
        }
    });
}

// Function to update PHP session with microphoneAllowed status
function updateMicrophoneAllowedStatus(allowed) {
    // Send AJAX request to update PHP session variable
    $.ajax({
        url: 'update-session.php',
        method: 'POST',
        data: { microphoneAllowed: allowed },
        success: function (response) {
            console.log('Microphone permission set in session.');
        },
        error: function (xhr, status, error) {
            console.error('Error setting microphone permission in session.');
        }
    });
}

// Example: Update PHP session when permissions are allowed or denied
function monitorPermissions() {
    // Example: Monitor webcam permission
    setInterval(function () {
        if (webcamAllowed && videoStream && videoStream.getVideoTracks().length > 0 && videoStream.getVideoTracks()[0].enabled) {
            webcamAllowed = true;
            updateWebcamAllowedStatus(true);
        } else {
            webcamAllowed = false;
            updateWebcamAllowedStatus(false);
        }
    }, 5000); // Check every 5 second

    // Example: Monitor microphone permission
    setInterval(function () {
        if (microphoneAllowed && audioStream && audioStream.getAudioTracks().length > 0 && audioStream.getAudioTracks()[0].enabled) {
            microphoneAllowed = true;
            updateMicrophoneAllowedStatus(true);
        } else {
            microphoneAllowed = false;
            updateMicrophoneAllowedStatus(false);
        }
    }, 5000); // Check every 5 second

    // Fetch session variables every 5 seconds
    setInterval(fetchSessionVariables, 5000);
}

// Example function to check permissions
function checkPermissions() {
    const nextButton = document.getElementById('next');
    if (nextButton){
        if (webcamAllowed && microphoneAllowed && secondaryCamAllowed) {
            nextButton.classList.remove('disabled-next-button');
            nextButton.disabled = false;// Capture a photo and audio every 60 seconds
        } else {
            nextButton.classList.add('disabled-next-button');
            nextButton.disabled = true;
        }
    }
}

// Function to fetch session variables and update UI based on permissions
async function fetchSessionVariables() {
    await $.ajax({
        url: 'get-session.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('Session variables:', response);
            // Use session variables
            webcamAllowed = response.webcamAllowed;
            microphoneAllowed = response.microphoneAllowed;
            secondaryCamAllowed = response.secondaryCamAllowed;
            updatesecondaryCamAllowedStatusText();
            checkPermissions();
            updateUIBasedOnPermissions();
        },
        error: function (xhr, status, error) {
            console.error('Error fetching session variables:', error);
        }
    });
}

function updatesecondaryCamAllowedStatusText() {
    const secondary_camera_input_text = document.getElementById('secondary_camera_input_text');
    if (secondary_camera_input_text){
        if( secondaryCamAllowed ){
            secondary_camera_input_text.innerHTML = "ALLOWED";
        } else {
            secondary_camera_input_text.innerHTML = "NOT ALLOWED";
        }
    }
}

function onAllowPermission() {
    document.getElementById('qr-code-container').style.display = 'block';
    startWebcam();
    startMicrophone();
}

async function startCapturing(){
    await fetchSessionVariables();
    if (webcamAllowed && microphoneAllowed && secondaryCamAllowed) {
        startWebcam();
        startMicrophone();
        setInterval(() => {
            capturePhoto();
            startRecording();
        }, 30000);
    }
}

function stopCapturing() {
    // Stop video stream
    if (videoStream && videoStream.getVideoTracks().length > 0) {
        videoStream.getVideoTracks().forEach(track => {
            track.stop();
        });
        videoStream = null;
        webcamAllowed = false;
        updateWebcamAllowedStatus(false);
    }

    // Stop audio stream and media recorder
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
    if (audioStream && audioStream.getAudioTracks().length > 0) {
        audioStream.getAudioTracks().forEach(track => {
            track.stop();
        });
        audioStream = null;
        microphoneAllowed = false;
        updateMicrophoneAllowedStatus(false);
    }
}


// Function to format time as mm:ss
function formatTime(seconds) {
    var min = Math.floor(seconds / 60);
    var sec = seconds % 60;
    return min.toString().padStart(2, '0') + ':' + sec.toString().padStart(2, '0');
}

// Function to update timer every second
function updateTimer(duration, startTime, timerElement) {
    var currentTime = new Date().getTime();
    var elapsedTime = Math.floor((currentTime - startTime) / 1000);
    var timeLeft = (duration * 60) - elapsedTime; // Convert minutes to seconds

    var interval = setInterval(function () {
        if (timeLeft > 0) {
            timerElement.innerHTML = formatTime(timeLeft);

            // Alert messages based on remaining time
            if (timeLeft === 300) { // 5 minutes remaining
                showNotification("5 Minutes remaining! Please submit the Google form before timer ends, to save the answers.");
            } else if (timeLeft === 180) { // 3 minutes remaining
                showNotification("3 Minutes remaining! Please submit the Google form before timer ends, to save the answers.");
            } else if (timeLeft === 60) { // 1 minute remaining
                showNotification("1 Minute remaining! Please submit the Google form now, to save the answers.");
            } else if (timeLeft === 30) { // 30 seconds remaining
                showNotification("30 Seconds remaining! Please submit the Google form now, to save the answers.");
            }

            timeLeft--;
        } else {
            timerElement.innerHTML = 'Time\'s up!';
            clearInterval(interval); // Stop the interval
            localStorage.removeItem('startTime'); // Clear localStorage
            finishExam(); // Auto-click the Finish Exam button
        }
    }, 1000);
}

function showNotification(message) {
    // Example: Show a custom notification instead of alert()
    var notificationElement = document.createElement('div');
    notificationElement.classList.add('custom-notification');
    notificationElement.textContent = message;
    document.body.appendChild(notificationElement);

    // Auto-remove notification after a few seconds
    setTimeout(function () {
        notificationElement.remove();
    }, 5000); // Remove after 5 seconds (adjust as needed)
}


function initTimer(duration) {
    var timerElement = document.getElementById('timer');
    var startTime = localStorage.getItem('startTime');

    // Start timer if it's not already running
    if (!startTime) {
        localStorage.setItem('startTime', new Date().getTime());
        startTime = localStorage.getItem('startTime');
    }

    // Resume timer if startTime is present
    updateTimer(duration, startTime, timerElement);
}

// function finishExam() {
//     // Stop video/audio inputs (if needed)
//     stopCapturing();

//     // Clear localStorage and redirect to dashboard.php
//     localStorage.removeItem('startTime');
//     window.location.href = '../candidate_dashboard/candidate_dashboard.php';
// }

// Example function to update UI based on permissions
function updateUIBasedOnPermissions() {
    const warningElement = document.getElementById('warning');
    const nextButton = document.getElementById('next');

    // Check if all permissions are allowed
    if (webcamAllowed && microphoneAllowed && secondaryCamAllowed) {
        warningElement.style.display = 'none'; // Hide warning message
        nextButton.classList.remove('disabled-next-button');
        nextButton.disabled = false; // Enable the NEXT button or resume activity
    } else {
        // Display warning message about missing permissions
        warningElement.style.display = 'block';
        warningElement.textContent = 'Please allow ';
        if (!webcamAllowed) {
            warningElement.textContent += 'webcam, ';
        }
        if (!microphoneAllowed) {
            warningElement.textContent += 'microphone, ';
        }
        if (!secondaryCamAllowed) {
            warningElement.textContent += 'secondary camera, ';
        }
        warningElement.textContent = warningElement.textContent.slice(0, -2) + ' to proceed.';
        nextButton.classList.add('disabled-next-button');
        nextButton.disabled = true; // Disable the NEXT button until all permissions are allowed
    }
}
