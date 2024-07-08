document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('candidate-form');

    form.addEventListener('submit', (event) => {
        // Optional: You can add custom form validation or other logic here if needed
        event.preventDefault(); // Prevent the default form submission

        const examId = new URLSearchParams(window.location.search).get('exam_id'); // Get exam_id from the URL
        if (examId) {
            form.action = `process_form.php?exam_id=${examId}`; // Set the form action with exam_id
        }
        form.submit(); // Submit the form
    });
});



// document.getElementById('candidate-form').addEventListener('submit', function(event) {
//     event.preventDefault();

//     const examinerId = document.getElementById('examiner-id').textContent;
//     const courseName = document.getElementById('course-name').textContent;
//     const batch = document.getElementById('batch').textContent;
//     const candidateId = document.getElementById('candidate-id').value;
//     const candidateName = document.getElementById('candidate-name').value;

//     // Store data in local storage
//     localStorage.setItem('examinerId', examinerId);
//     localStorage.setItem('courseName', courseName);
//     localStorage.setItem('batch', batch);
//     localStorage.setItem('candidateId', candidateId);
//     localStorage.setItem('candidateName', candidateName);

//     // Redirect to view-result page
//     window.location.href = 'process_form.php';
// });

