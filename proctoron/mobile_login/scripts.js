const videoElement = document.getElementById('videoElement');
const canvasElement = document.getElementById('canvasElement');

let videoStream, mediaRecorder;
let secondaryCamAllowed = false;

async function startWebcam() {
    const secondary_camera_input_text = document.getElementById('secondary_camera_input_text');
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
        secondary_camera_input_text.innerHTML = "ALLOWED";
        videoElement.srcObject = videoStream;
        secondaryCamAllowed = true;
        monitorPermissions();
    } catch (error) {
        console.error('Error accessing webcam:', error);
        secondary_camera_input_text.innerHTML = "NOT ALLOWED";
        alert('Error accessing webcam. Please ensure it is connected and permissions are granted.');
    }
    checkPermissions();
}

function capturePhoto() {
    if (secondaryCamAllowed && videoStream && videoStream.getVideoTracks().length > 0 && videoStream.getVideoTracks()[0].enabled) {
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


// Function to update PHP session with secondaryCamAllowed status
function updatesecondaryCamAllowedStatus(allowed) {
    // Send AJAX request to update PHP session variable
    $.ajax({
        url: '../input_full/update-session.php',
        method: 'POST',
        data: { secondaryCamAllowed: allowed },
        success: function (response) {
            console.log('Secondary camera permission set in session.');
        },
        error: function (xhr, status, error) {
            console.error('Error setting webcam permission in session.');
        }
    });
}


// Example: Update PHP session when permissions are allowed or denied
function monitorPermissions() {
    // Example: Monitor webcam permission
    setInterval(function () {
        if (secondaryCamAllowed && videoStream && videoStream.getVideoTracks().length > 0 && videoStream.getVideoTracks()[0].enabled) {
            secondaryCamAllowed = true;
            updatesecondaryCamAllowedStatus(true);
        } else {
            secondaryCamAllowed = false;
            updatesecondaryCamAllowedStatus(false);
        }
    }, 1000); // Check every second

    // Fetch session variables every 5 seconds
    setInterval(fetchSessionVariables, 5000);
}

// Example function to check permissions
function checkPermissions() {
    if (secondaryCamAllowed) {
        setInterval(() => {
            capturePhoto();
        }, 30000);
    }
}

// Function to fetch session variables
function fetchSessionVariables() {
    $.ajax({
        url: '../input_full/get-session.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('Session variables:', response);
            // Use session variables
            secondaryCamAllowed = response.secondaryCamAllowed;
            checkPermissions();
        },
        error: function (xhr, status, error) {
            console.error('Error fetching session variables:', error);
        }
    });
}


