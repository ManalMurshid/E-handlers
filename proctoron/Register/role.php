<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Selection</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="image-section">
            <!-- Image is set through CSS background-image -->
        </div>
        <div class="form-section">
            <h2>What is your role in this proctoring system?</h2>
            <form id="roleForm">
                <div class="form-group">
                    <label>
                        <input type="radio" name="role" value="examiner" required> Examiner
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="role" value="candidate" required> Candidate
                    </label>
                </div>
                <button type="submit" class="submit-btn">Continue</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('roleForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const role = document.querySelector('input[name="role"]:checked').value;
            if (role === 'examiner') {
                window.location.href = 'examiner_reg/examiner_reg.php';
            } else if (role === 'candidate') {
                window.location.href = 'candidate_reg/candidate_reg.php';
            }
        });

        // Reset the form when the page loads
        window.addEventListener('load', function() {
            document.getElementById('roleForm').reset();
        });
    </script>
</body>
</html>
